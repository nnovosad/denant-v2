<?php

class AnyWay_PhpEmbeddedFsArchive extends AnyWay_PhpEmbeddedFs
{
    // https://sourceforge.net/p/picard/code/HEAD/tree/trunk/src/java/net/sf/samtools/util/BlockCompressedStreamConstants.java#l55
    // DONT EVER TRY TO CHANGE DEFAULT_UNCOMPRESSED_BLOCK_SIZE TO 65536 OR SOMETHING NOT DIVIDING TO 512
    // AS DECOMPRESS CANNOT HANDLE THAT
    const DEFAULT_UNCOMPRESSED_BLOCK_SIZE = 65024; // http://sourceforge.net/p/samtools/mailman/message/29894810/
    // it used to be 65536, but $bsize = pack("v", strlen($compressed) + 25); so it must be 25 bytes shorter or else an overflow might happen
    // however DEFAULT_UNCOMPRESSED_BLOCK_SIZE = 65024 produces 65034 bytes at most
    const DEFAULT_COMPRESSED_BLOCK_SIZE = 65510;

    protected static $_magic = "\x1f\x8b\x08\x04";
    protected static $_header = "\x1f\x8b\x08\x04\x00\x00\x00\x00\x00\xff\x06\x00BC\x02\x00";
    protected static $_eof = "\x1f\x8b\x08\x04\x00\x00\x00\x00\x00\xff\x06\x00BC\x02\x00\x1b\x00\x03\x00\x00\x00\x00\x00\x00\x00\x00\x00";
    protected $_buffer = '';

    public function __construct($filename)
    {
        parent::__construct($filename);
        $this->_buffer = '';
    }

    public function fopen($filename, $mode)
    {
        throw new Exception("Use gzopen instead");
    }

    public function fwrite($handle, $data, $length = null)
    {
        throw new Exception("Use gzwrite instead");
    }

    public function fseek($handle, $offset, $mode = SEEK_SET)
    {
        throw new Exception("Use gzseek instead");
    }

    public function ftell($handle)
    {
        throw new Exception("Use gztell instead");
    }

    public function fread($handle, $length)
    {
        throw new Exception("Use gzreadblock instead");
    }

    public function fclose($handle, $write_fat = true)
    {
        throw new Exception("Use gzclose instead");
    }

    public function fflush($handle)
    {
        throw new Exception("Use gzflush instead");
    }

    public function gzopen($filename, $mode)
    {
        return parent::fopen($filename, $mode);
    }

    private function _write_block($handle, $block)
    {
        $blocklen = strlen($block);

        // this is redundant, in case
        if ($blocklen > self::DEFAULT_UNCOMPRESSED_BLOCK_SIZE)
            throw new Exception("Unable to handle blocks larger than " . self::DEFAULT_UNCOMPRESSED_BLOCK_SIZE);

        $compressed = gzdeflate($block, 9);

        if (strlen($compressed) >= self::DEFAULT_COMPRESSED_BLOCK_SIZE) {
            //throw new Exception("TODO - Didn't compress enough, try less data in this block");
            $chunks = str_split($block, self::DEFAULT_UNCOMPRESSED_BLOCK_SIZE - 1024);
            $result = 0;
            foreach ($chunks as $chunk) {
                $result += $this->_write_block($handle, $chunk);
            }
            return $result;
        }

        // https://github.com/attractivechaos/klib/blob/master/bgzf.c
        // compressed_length += BLOCK_HEADER_LENGTH + BLOCK_FOOTER_LENGTH; 18 + 8

        $bsize = pack("v", strlen($compressed) + 25); // unsigned short 2 bytes
        $crc = pack("V", crc32($block)); // unsigned int 4 bytes
        $uncompressed_length = pack("V", strlen($block)); // unsigned int 4 bytes

        /*
        # Fixed 16 bytes,
        # gzip magic bytes (4) mod time (4),
        # gzip flag (1), os (1), extra length which is six (2),
        # sub field which is BC (2), sub field length of two (2),
        # "\x1f\x8b\x08\x04"
        # "\x00"\x00\x00\x00" mod time
        # "\x00" gzip flag
        # "\xff" os
        # "\x06\x00" extra length
        # "\x42\x43" BC
        # "\x02\x00" subfield length;
        # Variable data,
        # 2 bytes: block length as BC sub field (2)
        # X bytes: the data
        # 8 bytes: crc (4), uncompressed data length (4)
        */
        $data = self::$_header . $bsize . $compressed . $crc . $uncompressed_length;
        $result = parent::fwrite($handle, $data);

        // for tests only
        if (isset($GLOBALS["anyway_iosleep"])) usleep($GLOBALS["anyway_iosleep"] * 1000000);
        return $result;
    }


    public function gzwrite($handle, $data)
    {
        $this->_buffer .= $data;
        while (strlen($this->_buffer) >= self::DEFAULT_UNCOMPRESSED_BLOCK_SIZE) {
            $this->_write_block($handle, substr($this->_buffer, 0, self::DEFAULT_UNCOMPRESSED_BLOCK_SIZE));
            $this->_buffer = substr($this->_buffer, self::DEFAULT_UNCOMPRESSED_BLOCK_SIZE);
        }
        return strlen($data);
    }

    public function gzflush($handle)
    {
        while (strlen($this->_buffer)) {
            $this->_write_block($handle, substr($this->_buffer, 0, self::DEFAULT_UNCOMPRESSED_BLOCK_SIZE));
            $this->_buffer = substr($this->_buffer, self::DEFAULT_UNCOMPRESSED_BLOCK_SIZE);
        }
        return true;
    }

    public function gzclose($handle, $writeEof = false)
    {
        if ($this->fh_appending === $handle) {
            $this->gzflush($handle);

            if ($writeEof) {
                parent::fwrite($handle, self::$_eof);
            }

            fseek($handle, 0, SEEK_END);
            $position = parent::ftell($handle);
            $result = parent::fclose($handle);
            return $result ? $position : false;
        }
        return parent::fclose($handle);
    }

    public function gzseek($handle, $offset, $mode = SEEK_SET)
    {
        return parent::fseek($handle, $offset, $mode);
    }

    public function gztell($handle)
    {
        return parent::ftell($handle);
    }

    public function gzreadblock($handle)
    {
        if (false === ($_magic = parent::fread($handle, 4)))
            return false;

        if ($_magic != self::$_magic)
            throw new Exception("Magic header not found");

        $_header = parent::fread($handle, 8);

        $t = unpack("Vgzip_mod_time/Cgzip_extra_flags/Cgzip_os/vextra_len", $_header);
        $extra_len = $t['extra_len'];

        $block_size = null;
        $x_len = 0;
        while ($x_len < $extra_len) {
            $_subfield_id = parent::fread($handle, 2);
            $_subfield_len = parent::fread($handle, 2);

            $t = unpack("vsubfield_len", $_subfield_len);
            $_subfield = parent::fread($handle, $t['subfield_len']);

            $x_len += $t['subfield_len'] + 4;
            if ($_subfield_id === "BC") {
                if ($t['subfield_len'] !== 2)
                    throw new Exception("Wrong BC payload length");
                if ($block_size !== null)
                    throw new Exception("Two BC subfields?");
                $t = unpack("vblock_size", $_subfield);
                $block_size = $t['block_size'];
            }
        }

        /*
        # Fixed 16 bytes,
        # "\x1f\x8b\x08\x04"
        # "\x00"\x00\x00\x00" mod time
        # "\x00" gzip flag
        # "\xff" os
        # "\x06\x00" extra length <-- $_header
        # "\x42\x43" BC <-- $_subfield_id
        # "\x02\x00" subfield length; <-- $_subfield_len
        # Variable data,
        # 2 bytes: block length as BC sub field (2) <- $block_size
        # X bytes: the data
        # 8 bytes: crc (4), uncompressed data length (4)
        */

        if ($x_len !== $extra_len)
            throw new Exception(sprintf("Extra len %d, not %d", $x_len, $extra_len));

        if ($block_size === null)
            throw new Exception("Missing BC, this isn't a BGZF file!");

        $deflate_size = $block_size - 25;

        $_data = parent::fread($handle, $deflate_size);

        $data = gzinflate($_data);

        $_expected_crc = parent::fread($handle, 4);
        $_expected_size = parent::fread($handle, 4);

        $t = unpack("Vexpected_size", $_expected_size);
        if ($t['expected_size'] !== strlen($data)) {
            $block = join('-', array(
                bin2hex($_magic),
                bin2hex($_header),
                bin2hex($_subfield_id),
                bin2hex($_subfield_len),
                bin2hex($_subfield),
                strlen($_data),
                bin2hex($_expected_crc),
                bin2hex($_expected_size)
            ));
            throw new Exception(sprintf("Decompressed to '%s', not '%s', block '%s' at position %d", strlen($data), $t['expected_size'], $block, parent::ftell($handle)));
        }

        $crc = pack("V", crc32($data));

        if ($_expected_crc !== $crc)
            throw new Exception(sprintf("CRC is %s, not %s", $crc, $_expected_crc));

        // for tests only
        if (isset($GLOBALS["anyway_iosleep"])) usleep($GLOBALS["anyway_iosleep"] * 1000000);

        return $data;
    }

    public function close()
    {
        if (is_resource($this->fh)) {
            if ($this->opened_for_write) {
                while ($this->FAT_fhlookup) {
                    $handle = reset($this->FAT_fhlookup);
                    if (false === $this->gzclose($handle))
                        throw new Exception("Unable to close handle");
                }
                // this will be flushed in parent::close();
                // $this->_write_fat();
            }
            parent::close();
        }
    }
}