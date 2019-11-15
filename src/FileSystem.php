<?php 
// this class contains methods for working with the file system

class FileSystem {

    public static $FILE_TYPE_TEXT = 0;

    public static $FILE_TYPE_JSON = 1;

    public static $FILE_TYPE_JPEG = 2;


    public static function list($pattern) {

        $files = glob($pattern);

        return $files;
    }

    // takes a path to a file and the file extension (both as strings) and converts the file into a php object.
    // Only works with .JSON files (for now)
    public static function toObject($path, $type) {

        if($type == FIleSystem::$FILE_TYPE_JSON) {
            $handle = fopen ($path, "r");
            $json = fread($handle,filesize($path));
            $object = json_decode($json);
    
            return $object;
        } else {
            throw new Exception();
        }
    }

    // takes an object, a name for the new file, a save path for the new file, and the new file's extension
    public static function save($object, $name, $savePath, $type) {
        
        if($type == FileSystem::$FILE_TYPE_JSON) {
            $handle = fopen($savePath.$name.".json", 'w');
            fwrite($handle, json_encode($object, JSON_UNESCAPED_SLASHES));
            fclose($handle);

            return $savePath.$name.$type;
        } else {
            throw new Exception();
        }
    }

}