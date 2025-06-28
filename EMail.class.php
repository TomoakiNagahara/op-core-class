<?php
/**	op-core-class:/EMail.class.php
 *
 * @created    2015-04-08
 * @updated    2016-11-17
 * @author     Tomoaki Nagahara
 * @copyright  Tomoaki Nagahara All right reserved.
 */

/**	namespace
 *
 */
namespace OP;

/**	EMail
 *
 * <pre>
	$mail = new EMail();
	$mail->From( $mail->GetLocalAddress(), 'From name');
	$mail->To('info@example.com', 'To name');
	$mail->Subject('Title');
	$mail->Content($content, $mime='text/plain');
	$mail->Attachment($file_path='app:/test.jpg', $mime='image/jpeg', $file_name='TEST.JPG');
	$mail->Send();
 * </pre>
 *
 * @created    2015-04-08
 * @updated    2016-11-17
 * @version    1.0
 * @package    op-core
 * @subpackage class
 * @author     Tomoaki Nagahara
 * @copyright  Tomoaki Nagahara All right reserved.
 */
class EMail
{
	/**	trait.
	 *
	 */
	use OP_CORE, OP_CI;

	//	...
	private $_head	 = [];
	private $_body	 = [];
	private $_debug	 = [];

	/**	Set from header.
	 *
	 * @param string $addr
	 * @param string $name
	 */
	function From(string $addr, ?string $name=null)
	{
		$this->_set_addr($addr, $name, 'from');
	}

	/**	Set to header.
	 *
	 * @param string $addr
	 * @param string $name
	 */
	function To(string $addr, ?string $name=null)
	{
		$this->_set_addr($addr, $name, 'to');
	}

	/**	Set cc header.
	 *
	 * @param string $addr
	 * @param string $name
	 */
	function Cc(string $addr, ?string $name=null)
	{
		$this->_set_addr($addr, $name, 'cc');
	}

	/**	Set bcc header.
	 *
	 * @param string $addr
	 * @param string $name
	 */
	function Bcc(string $addr)
	{
		$this->_set_addr($addr, '', 'bcc');
	}

	/**	Set reply-to header.
	 *
	 * @param string $addr
	 * @param string $name
	 */
	function ReplyTo(string $addr, ?string $name=null)
	{
		$this->_set_addr($addr, $name, 'reply-to');
	}

	/**	Set errors-to header.
	 *
	 * @param string $addr
	 * @param string $name
	 */
	function ErrorsTo(string $addr, ?string $name=null)
	{
		$this->_set_addr($addr, $name, 'errors-to');
	}

	/**	Set return-path header.
	 *
	 * @param string $addr
	 * @param string $name
	 */
	function ReturnPath(string $addr, ?string $name=null)
	{
		$this->_set_addr($addr, $name, 'return-path');
	}

	/**	Set subject(mail title) header.
	 *
	 * @param string $addr
	 * @param string $name
	 */
	function Subject(string $subject)
	{
		$this->_head['subject'] = $subject;
	}

	/**	Set content by mime.
	 *
	 * @param string $addr
	 * @param string $name
	 */
	function Content(string $content, string $mime='text/plain')
	{
		if( preg_match('|[^-_a-z0-9/]|', $mime) ){
			$this->_SetError("Mime was wrong. ($mime)");
			return false;
		}

		$body = [];
		$body['body'] = $content;
		$body['mime'] = $mime;
		$this->_body[] = $body;
	}

	/**	Set attachment files.
	 *
	 * @param string $addr
	 * @param string $name
	 */
	function Attachment(string $file_path, ?string $mime=null, ?string $file_name=null)
	{
		if(!file_exists($file_path)){
			throw OpException("Does not exists this file. ($file_path)");
		}
		$content = file_get_contents($file_path);

		if(!$file_name){
			$temp = explode(DIRECTORY_SEPARATOR, DIRECTORY_SEPARATOR.$file_path);
			$file_name = array_pop($temp);
		}

		if(!$mime){
			if( function_exists("finfo_file") ){
				$finfo = finfo_open(FILEINFO_MIME_TYPE);
				$mime  = finfo_file($finfo, $file_path);
				finfo_close($finfo);
			}else if(!$mime = exec('file -ib '.$file_path)){
				$mime = mime_content_type($file_path);
			}
		}

		$body = [];
		$body['body'] = $content;
		$body['mime'] = $mime;
		$body['name'] = $file_name;
		$this->_body[] = $body;
	}

	/**	Send mail.
	 *
	 * @param  string $type mta, socket, php
	 * @return boolean
	 */
	function Send(?string $type=null, string $language='uni', string $charset='utf-8')
	{
		//	...
		if( empty($this->_debug['sent']) ){
			$this->_debug['sent'] = true;
		}else{
			$message = "EMail was already sent. In the case of next e-mail transmission, please generate a new object.";
			$this->_set_error($message);
			return false;
		}

		//	...
		$save_lang = mb_language();
		$save_char = mb_internal_encoding();

		//	...
		mb_language($language);
		mb_internal_encoding($charset);

		//	...
		switch($type){
			case 'mta':
				$io = $this->_mta();
				break;

			case 'socket':
				$io = $this->_socket();
				break;

			case 'php':
			default:
				$io = $this->_mail();
		}

		//	...
		mb_language($save_lang);
		mb_internal_encoding($save_char);

		return $io;
	}

	/**	Show debug information for developer.
	 *
	 */
	function Debug()
	{
	//	D($this->_debug);
	}

	/**	Use PHP's mail function.
	 *
	 * @return boolean
	 */
	private function _mail()
	{
		//	CI
		$app_id = OP::AppID();
		switch( strtolower($app_id) ){
			case 'ci':
			case 'self-check':
				return true;
		}

		//	init
		$to		 = $this->_get_to();
		$subject = $this->_get_subject();
		$content = $this->_get_content();
		$headers = $this->_get_headers();
		$parameters = $this->_get_parameters();

		//	Send mail.
		if(!$io = mail($to, $subject, $content, $headers, $parameters)){
			$message = 'Failed to send the error mail.';
			$this->_set_error($message);
		}

		//	Debug
		$this->_debug['io'] = $io;
		$this->_debug['method'] = __METHOD__;
		$this->_debug['to'] = $to;
		$this->_debug['subject'] = $subject;
		$this->_debug['content'] = $content;
		$this->_debug['headers'] = $headers;
		$this->_debug['parameters'] = $parameters;
		$this->_debug['head'] = $this->_head;
		$this->_debug['body'] = $this->_body;

		//	...
		return $io;
	}

	/**	Use socket.
	 *
	 * @return boolean
	 */
	private function _socket()
	{
		return false;
	}

	/**	Use mta.
	 *
	 * @return boolean
	 */
	private function _mta()
	{
		return false;
	}

	/**	Generate email headers.
	 *
	 * @return string
	 */
	private function _get_headers()
	{
		$content_type = $this->_get_content_type();
		$mail_address = $this->_get_mail_address();
		return trim($content_type)."\n".trim($mail_address)."\n";
	}

	/**	Generate email addresses.
	 *
	 * @param  string $keys
	 * @return string
	 */
	private function _get_mail_address()
	{
		//	...
		$header = [];

		//	...
		$header[] = "X-SENDER: onepiece-framework:EMail";

		//	...
		foreach(['from','cc','bcc','reply-to','return-path','errors-to'] as $key){
			//	...
			if( empty($this->_head[$key]) ){ continue; }

			//	...
			$full_name = [];
			foreach($this->_head[$key] as $temp){
				$addr = $temp['addr'];
				$name = $temp['name'];
				$full_name[] = $this->_get_full_name($addr, $name);
			}

			//	...
			if( $full_name ){
				$header[] = ucfirst($key).': '.join(', ', $full_name);
			}
		}

		//	...
		return join("\n", $header);
	}

	/**	Get to address.
	 *
	 *  @return string
	 */
	private function _get_to()
	{
		$join = [];
		foreach($this->_head['to'] as $temp){
			$addr = $temp['addr'];
			$name = $temp['name'] ?? '';
			$join[] = $this->_get_full_name($addr, $name);
		}
		return join(', ',$join);
	}

	/**	Generate named e-mail address.
	 *
	 * @param  string $addr
	 * @param  string $name
	 * @return string
	 */
	private function _get_full_name(string $addr, ?string $name)
	{
		$addr = trim($addr);
		if( $name ){
			$name = mb_encode_mimeheader($name);
			$full_name = trim("$name <$addr>");
		}else{
			$full_name = $addr;
		}
		return $full_name;
	}

	/**	Get valid email address.
	 *
	 * @return string
	 */
	static function GetLocalAddress()
	{
		return get_current_user().'@'.gethostbyaddr($_SERVER['SERVER_ADDR'] ?? '127.0.0.1');
	}

	/**	Get php sendmail function's parameters.
	 *
	 * @return string
	 */
	private function _get_parameters()
	{
		if(!$addr = ifset($this->_head['from'][0]['addr']) ){
			$addr = self::GetLocalAddress();
		}

		//	...
		$parameters = "-f $addr";

		//	...
		return $parameters;
	}

	/**	Get boundary for multi-part.
	 *
	 * @return string
	 */
	private function _get_boundary()
	{
		static $boundary;
		if(!$boundary){
			/*
			$boundary = "--onepiece-framework--Boundary--" . uniqid("b");
			*/
			$subject  = $this->_get_subject();
			$unique   = OP::isCi() ? 'ci': md5($subject);
			$boundary = "--boundary--" . $unique;
		}
		return $boundary;
	}

	/**	Get content type.
	 *
	 * @return string
	 */
	private function _get_content_type()
	{
		$multipart = null;
		$mime_version = 'MIME-Version: 1.0';

		if( count($this->_body) > 1 ){
			$multipart = true;
			$boundary  = $this->_get_boundary();
		}else{
			$mime = isset($this->_body[0]['mime']) ? $this->_body[0]['mime']: 'text/plain';
		}

		if( $multipart ){
			$content_type = "Content-Type: Multipart/Mixed; boundary=\"$boundary\"";
			$content_encoding = 'Content-Transfer-Encoding: Base64';
		}else{
			$content_type = "Content-Type: {$mime}; charset=\"utf-8\"";
			$content_encoding = 'Content-Transfer-Encoding: 7bit';
		}

		return "{$mime_version}\n{$content_type}\n{$content_encoding}\n";
	}

	/**	Get mail content(body).
	 *
	 * @return string
	 */
	private function _get_content()
	{
		if( count($this->_body) > 1 ){
			$body = $this->_get_content_multipart();
		}else{
			$body = isset($this->_body[0]['body']) ? $this->_body[0]['body']: null;
		}
		return $body;
	}

	/**	Get content multi part.
	 *
	 * @return string
	 */
	private function _get_content_multipart()
	{
		$i = 0;
		$multibody = '';
		$boundary  = $this->_get_boundary();

		foreach($this->_body as $_body){
			$i++;
			$multibody .= "--{$boundary}\n";

			$mime = $_body['mime'];
			$body = $_body['body'];

			list($type,$ext) = explode('/', $mime);

			if( $type === 'text' ){
				$multibody .= "Content-Type: {$mime}; charset=\"utf-8\"\n";
				$multibody .= "Content-Transfer-Encoding: 7bit\n";
				$multibody .= "\n";
				$multibody .= "$body\n";
			}else{
				$attachment_name = "attachment-{$i}.{$ext}";
				$name = isset($_body['name']) ? $_body['name']: $attachment_name;
				$name = mb_convert_encoding( $name,'utf-8','ASCII, JIS, UTF-8, eucJP-win, SJIS-win'); // TODO: Japanese only
				$name = mb_encode_mimeheader($name,'utf-8');

			//	$multibody .= "Content-Type: application/octet-stream; name=\"{$name}\"\n";
				$multibody .= "Content-Type: {$mime}; name=\"{$name}\"\n";
				$multibody .= "Content-Disposition: attachment; filename=\"{$name}\"\n";
				$multibody .= "Content-Transfer-Encoding: base64\n";
				$multibody .= "\n";
				$multibody .= chunk_split( base64_encode($body) );
				$multibody .= "\n\n";
			}
		}

		$multibody .= "--{$boundary}--\n";
		return $multibody;
	}

	/**	Get subject(mail title).
	 *
	 * @return string
	 */
	private function _get_subject()
	{
		return mb_encode_mimeheader($this->_head['subject']);
	}

	/**	Set email address with name.
	 *
	 * @param string $addr
	 * @param string $name
	 * @param string $key
	 */
	private function _set_addr(string $addr, ?string $name, string $key)
	{
		$key  = strtolower($key);
		$addr = preg_replace('/\n/', '\n', $addr);
		$head = [];
		$head['addr'] = $addr;
		$head['name'] = $name;
		$this->_head[$key][] = $head;
	}

	/**	Set error message for developer.
	 *
	 * @param string $message
	 */
	private function _set_error(string $message)
	{
		$error = [];
		$error['message'] = $message;
		$error['backtrace'] = debug_backtrace();
		$this->_debug['errors'][] = $error;
	}
}
