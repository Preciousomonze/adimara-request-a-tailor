<?php
/**
 * File to easily zip all files and folders to be ready for plugin install
 */
// Get real path for our folder
$rootPath = realpath(__DIR__);
$plugin_name = 'adimara-request-a-tailor';
//first delete incase theres an old zip file
unlink($plugin_name.'.zip');
// Initialize archive object
$zip = new ZipArchive();
$zip->open($plugin_name.'.zip', ZipArchive::CREATE | ZipArchive::OVERWRITE);

// Create recursive directory iterator
/** @var SplFileInfo[] $files */
$files = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator($rootPath),
    RecursiveIteratorIterator::LEAVES_ONLY
);

foreach ($files as $name => $file){
    // Skip directories (they would be added automatically)
    if (!$file->isDir()){
        // Get real and relative path for current file
		$filePath = $file->getRealPath();
		$filePath = str_replace('\\','/',$filePath);//solved the repeated value of files with back slash :)
		//ignore .git
		if(strpos($filePath,'.git') !== false || strpos($filePath,'shortcode_text') !== false || strpos($filePath,'node_modules') !== false ){
			continue;
		}
		 $relativePath = $plugin_name.'/'.substr($filePath, strlen($rootPath) + 1);
		$relativePath = str_replace('\\','/',$relativePath);//replace back slash with forward slash,for proper directory
		// Add current file to archive
        $zip->addFile($filePath, $relativePath);
    }
}
// Zip archive will be created only after closing object
$zip->close();
echo "zipped\n";
//now remove some stuff inside the zip file, no better way
if($zip->open($plugin_name.'.zip')){
		$files_to_delete = ['zipper_file.php','README.md','package.json','Gruntfile.js','package-lock.json'];
	for($i = 0; $i < count($files_to_delete); $i++){
		echo "Deleting: ".$files_to_delete[$i]."...\n";
		var_dump($zip->deleteName($plugin_name.'/'.$files_to_delete[$i]));//delete this current file too
	}
	$zip->close();
	echo "necessary stuff deleted";
}
else{
	echo 'couldnt delete necessary stuff';
}