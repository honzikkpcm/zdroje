<?php  
      
	     $max_filesize = 5097152; // Maximum filesize in BYTES.  
         $allowed_filetypes = array('.jpg','.jpeg','.gif','.png','.pdf','.doc','.docx','.JPG','.JPEG','.GIF','.PNG','.PDF','.DOC','.DOCX'); // These will be the types of file that will pass the validation.  
         $filename = $_FILES['userfile']['name']; // Get the name of the file (including file extension).  
         $ext = substr($filename, strpos($filename,'.'), strlen($filename)-1); // Get the extension from the filename.  
         //$file_strip = str_replace(" ","_",$filename); //Strip out spaces in filename  
         $file_strip = utf2ascii($filename); //Strip out spaces in filename  
		 $upload_path = '../files/'; //Set upload path  
      
         // Check if the filetype is allowed, if not DIE and inform the user.  
        if(!in_array($ext,$allowed_filetypes)) {  
                die("<script> new Messi('Tento formát souboru není podporován. Soubor může být pouze JPG, PNG, GIF.', {title: 'Nesprávný formát souboru', modal: true, buttons: [{id: 0, label: 'Zavřít', val: 'X'}]}); </script>");  
        }  
       // Now check the filesize, if it is too large then DIE and inform the user.  
       if(filesize($_FILES['userfile']['tmp_name']) > $max_filesize) {  
                die("<script> new Messi('Tento soubor je příliš veliký. Soubor může být maximálně 5MB veliký', {title: 'Příliš veliký soubor', modal: true, buttons: [{id: 0, label: 'Zavřít', val: 'X'}]}); </script>");  
        }  
       // Check if we can upload to the specified path, if not DIE and inform the user.  
       if(!is_writable($upload_path)) {  
          die('<div class="error">You cannot upload to the /uploads/ folder. The permissions must be changed.</div>');  
        }  
         // Move the file if eveything checks out.  
         if(move_uploaded_file($_FILES['userfile']['tmp_name'],$upload_path . $file_strip)) {  
          echo '<div>'. $file_strip .'</div>'; // It worked.  
        } else {  
          echo '<span>'. $file_strip .'</span>'; // It failed :(.  
     }  

function utf2ascii($text)
    {
        $return = Str_Replace(
                        Array("á","č","ď","é","ě","í","ľ","ň","ó","ř","š","ť","ú","ů","ý ","ž","Á","Č","Ď","É","Ě","Í","Ľ","Ň","Ó","Ř","Š","Ť","Ú","Ů","Ý","Ž") ,
                        Array("a","c","d","e","e","i","l","n","o","r","s","t","u","u","y ","z","A","C","D","E","E","I","L","N","O","R","S","T","U","U","Y","Z") ,
                        $text);
        $return = Str_Replace(Array(" ", "_"), "-", $return); //nahradí mezery a podtržítka pomlčkami
        $return = Str_Replace(Array("(",")","!",",","\"","'"), "", $return); //odstraní ().!,"'
        $return = StrToLower($return); //velká písmena nahradí malými.       
		$rnd=date("Hi");
		$return = $rnd.$return;
		return $return;
    }

?>  