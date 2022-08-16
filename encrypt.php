<?php 

if (php_sapi_name() !== 'cli') {
    exit;
}

if( empty($argv[1]) || empty($argv[2]) || empty($argv[3])){
        die("usage: encrypt [source-folder] [key] [output-folder]");
        return;
    }
$src      = $argv[1];
$php_blot_key = $argv[2];
$encrypted = $argv[3];

$excludes = array('vendor');

foreach($excludes as $key => $file){
    $excludes[ $key ] = $src.'/'.$file;
}

$rec = new RecursiveIteratorIterator(new RecursiveDirectoryIterator( $src ));
$require_funcs = array('include_once', 'include', 'require', 'require_once'); 


foreach ($rec as $file) {

    if ($file->isDir()) {
        $newDir  = str_replace( $src, $encrypted, $file->getPath() );
        if( !is_dir( $newDir ) ) mkdir( $newDir );
        continue;
    };

    $filePath = $file->getPathname();

    if( pathinfo($filePath, PATHINFO_EXTENSION) != 'php'  ||
        in_array( $filePath, $excludes ) ) {  
        $newFile  = str_replace($src, $encrypted, $filePath );
        copy( $filePath, $newFile );
        continue;
    }

    $contents = file_get_contents( $filePath );
    $preppand = "<?php bolt_decrypt( __FILE__ , $php_blot_key); return 0;
    ##!!!##";
    $re = '/\<\?php/m';
    preg_match($re, $contents, $matches ); 
    if(!empty($matches[0]) ){
        $contents = preg_replace( $re, '', $contents );
        ##!!!##';
    }
    $cipher   = bolt_encrypt( $contents, $php_blot_key );
    $newFile  = str_replace($src, $encrypted, $filePath );
    $fp = fopen( $newFile, 'w');
    fwrite($fp, $preppand.$cipher);
    fclose($fp);

    unset( $cipher );
    unset( $contents );
}

$out_str       = substr_replace($src, '', 0, 4);
$file_location = __DIR__.$encrypted.$out_str;
echo "Successfully Encrypted... Please check in  " .$file_location."  folder.";



