<?php
$zip = new ZipArchive;
$res = $zip->open('backend.zip');
if ($res === TRUE) {
  $zip->extractTo(__DIR__);
  $zip->close();
  echo 'Extraction successful!';
} else {
  echo 'Error extracting zip file.';
}
?>
