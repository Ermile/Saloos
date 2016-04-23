<?php
/** In The name of Allah **/
/*  Proudly made in IRAN, powered by Saloos, under licence of Ermile */

/* vim: set expandtab tabstop=4 shiftwidth=4: */
// +----------------------------------------------------------------------+
// |                                                                      |
// +----------------------------------------------------------------------+
// | Copyright (c) 2014-2016 The Ermile Company                           |
// | 010001010111001001101101011010010110110001100101                     |
// +----------------------------------------------------------------------+
// | Authors: Original Author <author@example.com>                        |
// |          Javad Evazzadeh <J.Evazzadeh@live.com>                      |
// +----------------------------------------------------------------------+
// $Id:$
/**
 * Class and Function List:
 * Function list:
 * - read()
 * - write()
 * - append()
 * - makeDir()
 * - rename()
 * - move()
 * - copy()
 * - exists()
 * - delete()
 * - upload()
 * - getExtension()
 * - getName()
 * - getPath()
 * - getSize()
 * - humanReadableSize()
 * Classes list:
 * - File
 */
namespace lib\utility;

/** Files management : write, read, delete, upload... **/
class file
{

    /**
     * Reads a file and returns its content
     *
     * @param string $filepath	Path of the file
     * @return string|bool	Content of the file, or false on failure
     */
    public static function read( $filepath )
    {
        return file_get_contents( $filepath );
    }

    /**
     * Writes in a file
     *
     * @param string $filepath	Path of the file
     * @param string $content	Content of the file
     * @return int|bool	Number of bytes that were written to the file, or false on failure.
     */
    public static function write( $filepath, $content )
    {
        return file_put_contents( $filepath, $content, LOCK_EX );
    }

    /**
     * Writes at the end of a file
     *
     * @param string $filepath	Path of the file
     * @param string $content	Content of the file
     * @return int|bool	Number of bytes that were written to the file, or false on failure.
     */
    public static function append( $filepath, $content )
    {
        return file_put_contents( $filepath, $content, FILE_APPEND | LOCK_EX );
    }

    /**
     * Creates a new directory
     *
     * @param string $dirpath	Path of the new dir
     * @param int $mode			Change the mode of the dir
     * @param bool $recursive	Creates the dir recursively
     * @return bool	True on success, false on failure
     */
    public static function makeDir( $dirpath, $mode = 0775, $recursive = false )
    {
        return mkdir( $dirpath, $mode, $recursive );
    }

    /**
     * Renames a file or a directory
     *
     * @param string $oldname	Old name of the file / dir
     * @param string $newname	New name of the file / dir
     * @return bool	True on success, false on failure
     */
    public static function rename( $oldname, $newname )
    {
        return rename( $oldname, $newname );
    }

    /**
     * Moves a file or a directory
     *
     * @param string $path		Name of the file / dir
     * @param string $newdir	Name of the new parent dir
     * @return bool	True on success, false on failure
     */
    public static function move( $path, $newdir )
    {
        $newdir = rtrim( $newdir, '/' );
        return self::rename( $path, $newdir . '/' . basename( $path ) );
    }

    /**
     * Copies a file or a directory
     *
     * @param string $name		Name of the file / dir
     * @param string $copyname	Name of the copy of the file / dir
     * @return bool	True on success, false on failure
     */
    public static function copy( $name, $copyname )
    {

        // If it's a dir...
        if( is_dir( $name ) )
        {
            if( !self::makeDir( $copyname ) )
            {
                return false;
            }
            $handle = opendir( $name );

            while( $filename = readdir( $handle ) )
            {
                if( $filename != '.' && $filename != '..' )
                {
                    if( !self::copy( $name . '/' . $filename, $copyname . '/' . $filename ) )
                    {
                        self::delete( $copyname );
                        return false;
                    }
                }
            }
            closedir( $handle );
            return true;

            // If it's a file


        }
        else if( file_exists( $name ) )
        {
            return copy( $name, $copyname );
        }
        return false;
    }

    /**
     * Checks whether a file or directory exists
     *
     * @param string $path		Name of the file / dir
     * @return bool	True if the file or directory exists; False otherwise
     */
    public static function exists( $path )
    {
        return file_exists( $path );
    }

    /**
     * Deletes file or a directory recursively
     *
     * @param string $path	Name of the file / dir
     * @return bool	True on success, false on failure
     */
    public static function delete( $path )
    {

        // If it's a dir...
        if( is_dir( $path ) )
        {
            $handle = opendir( $path );

            while( $filename = readdir( $handle ) )
            {
                if( $filename != '.' && $filename != '..' )
                {
                    if( !self::delete( $path . '/' . $filename ) )return false;
                }
            }
            closedir( $handle );

            return rmdir( $path );

            // If it's a file


        }
        else if( file_exists( $path ) )
        {
            return unlink( $path );
        }
        return true;
    }

    /**
     * Recovers an uploaded file and store it in the tmp dir
     *
     * @param string $name	Name of the input[type=file] tag
     * @return string|array|bool	Path of the file in the tmp dir, or array of paths if multi-upload, false on failure
     */
    public static function upload( $name )
    {
        if( isset( $_FILES[ $name ] ) && isset( $_FILES[ $name ]['name'] ) && $_FILES[ $name ]['size'] != 0 )
        {

            // Multi-upload
            if( is_array( $_FILES[ $name ]['name'] ) )
            {
                $paths = array();

                for( $i = 0; $i < count( $_FILES[ $name ]['name'] ); $i++ )
                {
                    $path = DATA_DIR . Config::DIR_DATA_TMP . $_FILES[ $name ]['name'][ $i ];
                    if( move_uploaded_file( $_FILES[ $name ]['tmp_name'][ $i ], $path ) )
                    {
                        @chmod( $path, 0777 );
                        $paths[] = $path;
                    }
                }
                if( count( $paths ) != 0 )return $paths;

                // Single file


            }
            else
            {
                $path = DATA_DIR . Config::DIR_DATA_TMP . $_FILES[ $name ]['name'];
                if( move_uploaded_file( $_FILES[ $name ]['tmp_name'], $path ) )
                {
                    @chmod( $path, 0777 );
                    return $path;
                }
            }
        }
        return false;
    }

    /**
     * Returns the extension of a file
     *
     * @param string $filename	Name of the file
     * @return string	Extension of the file
     */
    public static function getExtension( $filename )
    {
        $pos = strrpos( $filename, '.' );
        return $pos === false ? '' : substr( $filename, $pos + 1 );
    }

    /**
     * Returns the name of a file
     *
     * @param string $filepath	Path of the file
     * @return string	Name of the file
     */
    public static function getName( $filepath )
    {
        return basename( $filepath );
    }

    /**
     * Returns the directory of a file
     *
     * @param string $filepath	Path of the file
     * @return string	Directory of the file
     */
    public static function getPath( $filepath )
    {
        return dirname( $filepath );
    }

    /**
     * Returns the size of a file
     *
     * @param string $filepath	Path of the file
     * @return int	Size of the file
     */
    public static function getSize( $filepath )
    {
        return filesize( $filepath );
    }

    /**
     * Returns a size readable by humans
     *
     * @param int $size	Size in bytes
     * @return string	Human readable size
     */
    public static function humanReadableSize( $_size, $accuracy = 0 )
    {
        if( $_size > 1024 * 1024 * 1024 )
            return round( $_size /( 1024 * 1024 * 1024 ), $accuracy ) . ' Go';

        if( $_size > 1024 * 1024 )
            return round( $_size /( 1024 * 1024 ), $accuracy ) . ' Mo';

        if( $_size > 1024 )
            return round( $_size / 1024, $accuracy ) . ' Ko';

        return $_size . ' octets';
    }

    /**
     * force browser to download file
     * @param  [type] $_filepath [description]
     * @return [type]           [description]
     */
    public static function download($_filePath, $_fileName = null, $_fileMime = null)
    {
        // Quick check to verify that the file exists
        if(!file_exists($_filePath))
        {
            // return false;
        }

        if(!$_fileName)
        {
            $_fileName = basename($_filePath);
        }

        if(!$_fileMime)
        {
            // get file mime from upload library
            $_fileMime = \lib\utility\upload::extCheck($_filePath);
            $_fileMime = $_fileMime['mime'];
        }

            // Force the download
            header('Pragma: public');
            header('Expires: 0');
            header('Cache-Control: no-cache, must-revalidate, post-check=0, pre-check=0');
            header('Last-Modified: '. gmdate ('D, d M Y H:i:s', filemtime ($_filePath)). ' GMT');
            header("Content-disposition: attachment; filename=\"" .basename($_fileName) ."\"");
            header('Content-Length: ' .filesize($_filePath));
            header('Content-Transfer-Encoding: binary');

        // Generate the server headers to force the download process
        if( strstr( $_SERVER['HTTP_USER_AGENT'], "MSIE" ) )
        {
            header("Content-Type: " .$_fileMime .";");
        }
        else
        {
            header( "Content-Type: ".$_fileMime, true, 200 );
            header('Cache-Control: private',false);
            header('Accept-Ranges: bytes');
            header('Connection: close');
        }

        readfile($_filePath);
        exit();
    }
}

?>