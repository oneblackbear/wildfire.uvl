<?php


class MediaImporter {
  
  public function import($filename, $file_type, $data) {
    if($filename && $data){
      //from the file name find the extension
      $ext = (substr(strrchr($filename,'.'),1));
      $check = strtolower($ext);
      //find the class associated with that file
      $setup = WildfireMedia::$allowed;

      if($setup && ($class= $setup[$check])){
        //save the file somewhere
        $path = PUBLIC_DIR. "files/".date("Y-m-W")."/";
        if(!is_dir($path)) mkdir($path, 0777, true);
        $filename = File::safe_file_save($path, $filename);
        file_put_contents($path.$filename, $data);
        //now we make a new media item
        $model = new WildfireMedia;
        $vars = array('title'=>basename($filename, ".".$ext),
                      'file_type'=>$file_type,
                      'status'=>0,
                      'media_class'=>$class,
                      'uploaded_location'=>str_replace(PUBLIC_DIR, "", $path.$filename),
                      'hash'=>hash_hmac('sha1', $data, md5($data)),
                      'ext'=>$ext
                      );
        if($saved = $model->update_attributes($vars)){
          $obj = new $class;
          $obj->set($saved);
          return $model;  
        }
      }
    }
  }
}