<?php

/**
 * Emma is part of the Tina4 stack which allows you to easily send emails from your system without crazy configuration, emma can also read emails from an inbox you may have
 * @todo Add the ability to parse email addresses
 *
 */
class Emma {
    
    private $bulkSMSUsername="";
    private $bulkSMSPassword="";
    
    
    /**
     * Constructor for Emma
     * 
     * @todo Add the ability to talk to a specific email server 
     */
    function __construct ($bulkSMSUsername="", $bulkSMSPassword="") {
       //maybe add some special settings for server etc 
        $this->bulkSMSUsername = $bulkSMSUsername;
        $this->bulkSMSPassword = $bulkSMSPassword;
    }
    
    /**
     * A function that will send a confirmation email to the user.
     * 
     * The sendMail function takes on a number of params and sends and email to a receipient.
     * 
     * @param String $recipient This can be a String or Array, the Array should be ; delimited
     * @param String $subject The subject for the email
     * @param String $message The message to send to the Receipient
     * @param String $fromName The name of the person sending the message
     * @param String $fromAddress The address of the person sending the message
     * @param String $attachments An Array of file paths to be attached in the form array ( array( "filename", "path" ) )
     * @return String OK or Failed
     */
    function sendMail ($recipient, $subject, $message, $fromName, $fromAddress, $attachments=null) {
        //define the headers we want passed. Note that they are separated with \r\n
		$boundary_rel = md5(uniqid(time()));
		$boundary_alt = md5(uniqid(time()));
		$eol = PHP_EOL;
		$headers = "MIME-Version: 1.0 {$eol}From:{$fromName}<{$fromAddress}>{$eol}Reply-To:{$fromAddress}{$eol}";
        $headers .= "Content-Type: multipart/related; boundary={$boundary_rel}{$eol}";		
		$headers .= "--{$boundary_rel}{$eol}Content-Type: multipart/alternative; boundary={$boundary_alt}{$eol}";
		
        $message = $this->prepareHtmlMail($message, $eol, "--".$boundary_rel, "--".$boundary_alt);	
                       
        try {        
            $mail_sent = @mail( $recipient, $subject, $message, $headers);
            file_put_contents(Ruth::getDOCUMENT_ROOT()."/../mailspool/email_".date("d_m_Y_h_i_s").".eml", $headers.$message);
        } catch(Exception $e) {
            $mail_sent =  false;
                    
        }
		
        //if the message is sent successfully print "Mail sent". Otherwise print "Mail failed" 
        return $mail_sent ? "OK" : "Failed";        
    }
    
    function prepareHtmlMail($html, $eol, $boundary_rel, $boundary_alt) {
		preg_match_all('~<img.*?src=.([\/.a-z0-9:;,+=_-]+).*?>~si',$html,$matches);
		
        $i = 0;
        $paths = array();

        foreach ($matches[1] as $img) {
			$img_old = $img;
			
            if(strpos($img, "http://") === false) {
                $paths[$i]['img'] = $img;
                $content_id = md5($img);
                $html = str_replace($img_old,'cid:'.$content_id,$html);
                $paths[$i++]['cid'] = $content_id;
            }
        }
		
        $multipart = '';
		$multipart .= "{$boundary_alt}{$eol}";
		$multipart .= "Content-Type: text/plain; charset=UTF-8{$eol}{$eol}{$eol}";
        $multipart .= "{$boundary_alt}{$eol}";
		$multipart .= "Content-Type: text/html; charset=UTF-8{$eol}{$eol}";
        $multipart .= "{$html}{$eol}{$eol}";
		$multipart .= "{$boundary_alt}--{$eol}";
		
        
        foreach ($paths as $key => $path) {
                        $message_part = "";    
                   
			$img_data = explode(",",$path["img"]);
			
			$imgdata = base64_decode($img_data[1]);
			
			$f = finfo_open();

			$mime_type = finfo_buffer($f, $imgdata, FILEINFO_MIME_TYPE);
			
			$filename = "image_{$key}";
			switch($mime_type){
				case "image/jpeg":
					$filename .= ".jpg";
				break;
				case "image/png":
					$filename .= ".jpg";
				break;
				case "image/gif":
					$filename .= ".jpg";
				break;
				default:
					$filename .= ".jpg";
				break;
			}
			
            $message_part .= "Content-Type: {$mime_type}; name=\"{$filename}\"{$eol}";
			$message_part .= "Content-Disposition: inline; filename=\"{$filename}\"{$eol}";
            $message_part .= "Content-Transfer-Encoding: base64{$eol}";			
            $message_part .= "Content-ID: <{$path['cid']}>{$eol}";
			$message_part .= "X-Attachment-Id: {$path['cid']}{$eol}{$eol}";
            $message_part .= $img_data[1];
            $multipart .= "{$boundary_rel}{$eol}".$message_part."{$eol}";
        }

        $multipart .= "{$boundary_rel}--";
        
        return $multipart;  
    }
    
    /**
     * Send SMS
     * @param String $mobileno
     * @param String $message
     * @param String $countryPrefix
     * @return String Result of SMS send
     */
    function sendSMS ($mobileno, $message="", $countryPrefix="27") {
        $celno = $this->formatMobile ($mobileno, $countryPrefix);
        $c = curl_init('http://bulksms.2way.co.za:5567/eapi/submission/send_sms/2/2.0');
        curl_setopt($c, CURLOPT_POST, 1);
        curl_setopt($c, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($c, CURLOPT_POSTFIELDS, 'concat_text_sms_max_parts=4&allow_concat_text_sms=1&username='.$this->bulkSMSUsername.'&password='.$this->bulkSMSPassword.'&message='.$message.'&msisdn='.$celno);
        $q=curl_exec($c);
        curl_close($c);
        $result = $q;
        return (stripos($result, "IN_PROGRESS") !== false) ? "OK" : "Failed";
    }
    
    /**
     * Format the Mobile Number
     * @param String $celno Mobile number to send with
     * @param String $countryPrefix 1 - america, 27 - south africa
     * @return string
     */
    function formatMobile($celno, $countryPrefix="27")
    {
        $ilen = strlen($celno);
        $tmpcel = '';
        $i = 0;
        while ($i < $ilen)
        {
          $val = substr($celno, $i,1);
          if (is_numeric($val))
          {
            $tmpcel = $tmpcel . substr($celno, $i,1);
          }
          $i ++;
        }

        $tmpcel = trim($tmpcel);
        if (substr($tmpcel, 0,1) === "0")
        {
          $tmpcel = substr_replace($tmpcel, $countryPrefix, 0, 1);
        }else if(strlen($tmpcel)< 11){
			$tmpcel = $countryPrefix.$tmpcel;
		}

        if ((strlen($tmpcel)< 11) || (strlen($tmpcel)>11)) {
          return "Failed";
        }
          else {
          return $tmpcel;
        }
    }
 
    
}