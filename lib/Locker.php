<?php
/**
 *
 * @package Locker
 */
/**
 * Locker Base Class.
 *
 * @author  Alfonso Marin <almarin@um.es>
 * @package Locker
 */

class Locker
{

	public static function listMyActiveFiles(){
		$storage = $GLOBALS['injector']->getInstance('Locker_Factory_Driver')->create();
		return $storage->listFiles(array(
			'status' => 'active'
		));
	}

	public static function listMyExpiredFiles(){
		$storage = $GLOBALS['injector']->getInstance('Locker_Factory_Driver')->create();
		return $storage->listFiles(array(
			'status' => 'expired'
		));
	}

	public static function listMyActiveGroups(){
		$storage = $GLOBALS['injector']->getInstance('Locker_Factory_Driver')->create();
		return $storage->listGroups();
	}
	public static function storeUploadedFile($fileinfo){
		$data = array(
			'file_id' => $fileinfo->name,
			'file_name' => $fileinfo->filename,
			'file_type' => $fileinfo->type,
			'file_size' => $fileinfo->size,
			'file_status' => 'uploaded');
		$file = new Locker_File($data);
		$file->create();
	}
	public static function isValidType($type){
		return (array_search($type, array('mail', 'twitter', 'facebook')) !== false);
	}

	public static function fileTypeToImg($type){
		static $icon_cache;


		if (empty($icon_cache[$type])) {
	            $icon_cache[$type] = Horde::img($GLOBALS['injector']->getInstance('Horde_Core_Factory_MimeViewer')->getIcon($type), '', '', '');
        }
       return $icon_cache[$type];
	}
	public static function getDownloadUrl($file){
		return 'download.php?file='.$file->id();
	}
	public static function loadCoreJavaScript(){
		$GLOBALS['page_output']->addScriptFile('jquery-1.9.1.min.js', 'locker');
		$GLOBALS['page_output']->addScriptFile('underscore.min.js', 'locker');
		$GLOBALS['page_output']->addScriptFile('backbone.min.js', 'locker');
		$GLOBALS['page_output']->addScriptFile('vendor/jquery.ui.widget.js', 'locker');
		$GLOBALS['page_output']->addScriptFile('jquery.iframe-transport.js', 'locker');
		$GLOBALS['page_output']->addScriptFile('jquery.fileupload.js', 'locker');
		$GLOBALS['page_output']->addScriptFile('jquery.ambiance.js', 'locker');
		$GLOBALS['page_output']->addScriptFile('locker.js', 'locker');
		
	}
	public static function getLockerPath(){
		//TODO coger FS consigna de config
		return '/var/consigna/a1/';

	}

  public static function readFileChunged($filename,$retbytes=true)
  {
   $chunksize = 1*(1024*1024); // how many bytes per chunk
   $buffer = '';
   $cnt =0;
   // $handle = fopen($filename, 'rb');
   $handle = fopen($filename, 'rb');
   if ($handle === false)
   {
     return false;
   }
   while (!feof($handle))
   {
     $buffer = fread($handle, $chunksize);
     echo $buffer;
  //   flush();
     if ($retbytes)
     {
       $cnt += strlen($buffer);
     }
   }
   $status = fclose($handle);
   if ($retbytes && $status)
   {
     return $cnt; // return num. bytes delivered like readfile() does.
   }
   return $status;
  }   

  public static function sendGroup($group){
      global $registry, $injector;
//echo "<pre>";print_r($group);exit;
      $icons = array();
      foreach ($group->getFiles() as $file){
        $img = $GLOBALS['injector']->getInstance('Horde_Core_Factory_MimeViewer')->getIcon($file->file_type);
        if (empty($icons[basename($img->fs)])){
          $image = new Horde_Mime_Part();
          $image->setType('image/png');
          $image->setContents(file_get_contents($img->fs));
          $image->setContentId(basename($img->fs));
          $image->setDisposition('attachment');

          $icons[basename($img->fs)] = $image;          
        }
      }

      $ident = $injector->getInstance('Horde_Core_Factory_Identity')->create($event->creator);
      if (!$ident->getValue('from_addr')) {
          $notification->push(sprintf(_("You do not have an email address configured in your Personal Information Preferences. You must set one %shere%s before event notifications can be sent."), $registry->getServiceLink('prefs', 'kronolith')->add(array('app' => 'horde', 'group' => 'identities'))->link(), '</a>'), 'horde.error', array('content.raw'));
          return;
      }


  		$view = $GLOBALS['injector']->createInstance('Horde_View');
      $view->group = $group;
      $multipart = new Horde_Mime_Part();
      $multipart->setType('multipart/alternative');
      $bodyText = new Horde_Mime_Part();
      $bodyText->setType('text/plain');
      $bodyText->setCharset('UTF-8');
      $bodyText->setContents($view->render('mail.plain.php'));
      $bodyText->setDisposition('inline');
      $multipart->addPart($bodyText);
      $bodyHtml = new Horde_Mime_Part();
      $bodyHtml->setType('text/html');
      $bodyHtml->setCharset('UTF-8');
      $bodyHtml->setContents($view->render('mail.html.php'));
      $bodyHtml->setDisposition('inline');
      $related = new Horde_Mime_Part();
      $related->setType('multipart/related');
      $related->setContentTypeParameter('start', $bodyHtml->setContentId());
      $related->addPart($bodyHtml);
      foreach ($icons as $icon){
        $related->addPart($icon);
      }
      
      

        $parser = new Horde_Mail_Rfc822();
        $list = $parser->parseAddressList($group->group_metadata->to);

      $multipart->addPart($related);
      
      $mail = new Horde_Mime_Mail(
          array('Subject' =>  '[consigna] ' . $group->group_metadata->subject,
                'To' => $list,
                'From' => $ident->getDefaultFromAddress(true),
                'User-Agent' => 'Consigna ' . $registry->getVersion()));
      $mail->setBasePart($multipart);

      try {
          $mail->send($injector->getInstance('Horde_Mail'));
          $GLOBALS['notification']->push(
              sprintf(_("The event notification to %s was successfully sent."), $recipient),
              'horde.success'
          );
          $success = true;
      } catch (Horde_Mime_Exception $e) {
          $GLOBALS['notification']->push(
              sprintf(_("There was an error sending an event notification to %s: %s"), $recipient, $e->getMessage(), $e->getCode()),
              'horde.error'
          );
          $success = false;
      }
      return $success;
  } 
}