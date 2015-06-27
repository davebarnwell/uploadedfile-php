uploadedfile
============

[![Build Status](https://scrutinizer-ci.com/g/freshsauce/uploadedfile-php/badges/build.png?b=master)](https://scrutinizer-ci.com/g/freshsauce/uploadedfile-php/build-status/master) [![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/freshsauce/uploadedfile-php/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/freshsauce/uploadedfile-php/?branch=master)

Handle an uploaded file

    try {
      $file = new UploadedFile('formFilename',array(IMAGETYPE_GIF, IMAGETYPE_JPEG, IMAGETYPE_PNG), 200000);
      if ($file->uploadOK()) {
        $file->save('/some/directory','somefile.'.$file->getExtension());
      }
    } catch (Exception $e) {
      // oversize or not valid image type
      echo 'Invalid';
    }
    