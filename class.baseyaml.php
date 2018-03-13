<?
/**
 * Baseyaml class by Martin-Pierre Frenette 
 * Published under the GPL licenses.
 * 
 * Website: www.mpfrenette.com
 * Email: mpfrenette@gmail.com
 * Phone number: GET-YOU-DATA (438-968-3282)
 * Toll Free: 1-877-FRENETTE (373-6388)
 * 
 * Created in under 2 hours for the 2 hour code Challenge: www.2hourcode.com
 *
 * This class is a very, very basic user storage class which saves in a yaml
 * file user data.
 *
 * It needs the yaml PECL library installed in PHP.
 *
 * It needs the file class.baseyaml.config.php which only has 2 constants:
 * BASEYAML_STORAGE_DIRECTORY : which indicates in which non-web accessible directory to place your files
 * BASEYAML_CONFIG_FILE: which indicates the file in that directory to store the default config in.
 *
 * The system is rather simple: you simply create an instance of the class and call the public functions.
 * 
 * The CreateUser function will either create a new user or login as that user if the username and password matches.
 * The LoginUser function will try login.
 *
 * Both will automatically call LoddUserFile.
 *
 * You can use getuservalue to query the storage, the key is your own application key, thus
 * allowing multiple systems to use the same data files.
 *
 * You can use setuservalue to set values in storage.
 *
 * If you modify values, make sure to call "saveuserfile" to commit to disk.
 *
 * It's that simple, but it was written in less than 2 hours... 
 */

define(  "BASEYAML_APP_KEY", '_baseyaml');

class baseyaml{

	// this is the class config itself
	var $config = array();

	// this contains the userdata when a user is logged in
	var $userdata = array();

	/**
	 * Base constructor for the class.
	 *
	 * Will ensure that the yaml PECL class is loaded, that the config file is present,
	 * and that the base config is created. Right now, the only thing we store in the 
	 * config is a seed to protect the user file name, but in the future, more might be
	 * present.
	 *
	 * Please note that right now, a simply echo and exit is used for error debugging,
	 * but an exception should be used...
	 */
	public function __construct(){

		if ( !extension_loaded('yaml')){
			echo 'Error: the YAML library is not loaded. You can use PECL to install it';
			exit();		

		}

		if ( is_file('class.baseyaml.config.php')){
			require_once('class.baseyaml.config.php');

			if ( !is_dir(BASEYAML_STORAGE_DIRECTORY) && !mkdir(BASEYAML_STORAGE_DIRECTORY) ){
				echo 'Error: class.baseyaml.php is not able to create the directory:'. BASEYAML_STORAGE_DIRECTORY;
				exit();		
			
			}
			if ( !is_file(BASEYAML_STORAGE_DIRECTORY. BASEYAML_CONFIG_FILE)){
				$this->CreateDefaultConfig();
			}
			$this->LoadConfig();
			
		}
		else{
			echo 'Error: class.baseyaml.config.php is not found';
			exit();
		}


	}
	/**
	 * At this moment, the config only stores a random seed.
	 */
	protected function CreateDefaultConfig(){
		
		$values= array();

		// for this purpose, we only need a random seed, so sha1 is enough.
		$values[BASEYAML_APP_KEY]['seed'] = sha1(openssl_random_pseudo_bytes(16));

		if ( !yaml_emit_file(BASEYAML_STORAGE_DIRECTORY. BASEYAML_CONFIG_FILE, $values) ){
			echo 'Error: unable to create the '. BASEYAML_STORAGE_DIRECTORY. BASEYAML_CONFIG_FILE. ' file. ';
			exit();

		}
	}
	/**
	 * This function, called in the constructor, simply loads the general config
	 */
	protected function LoadConfig(){
		$this->config = yaml_parse_file(BASEYAML_STORAGE_DIRECTORY. BASEYAML_CONFIG_FILE);
	}

	/**
	 * The userfile name is stored using the sha1 function, which is NOT secured, but it's enough
	 * to save a user file. Yes, a collision might occur, but it should be rather rare.
	 *
	 * The seed is added so that if the class is used in different places, it will be different
	 * user file names.
	 *
	 * Granted, the files are currently stored in plain text, but the load and save functions are
	 * public and could be overloaded to encrypt the content of the files.
	 * 
	 * @param string $username The username to load
	 */
	protected function GetUserFileName($username){
		return  BASEYAML_STORAGE_DIRECTORY. sha1($username . $this->config[BASEYAML_APP_KEY]['seed']). '.yaml';
	}

	/**
	 * This either creates the user with the username paramete, or, if the user
	 * already exists and the password matches, logs the user in.
	 *
	 * The email is also needed, but it's not really validated. Of course, the username
	 * can also be an email address.
	 *
	 * The password is encrypted with the password_hash function of PHP which is supposed
	 * to be secure. 
	 * 
	 * @param string $username the username which will be used to login. Can be an email
	 * @param string $email    the email address of the user
	 * @param string $password the password to login
	 * @return boolean : true if the user is created or logged in.
	 */
	public function CreateUser($username, $email, $password){

		$filename = $this->GetUserFileName($username);
		if (!is_file( $filename) ){
			$values = array();
			$values['username'] = $username;
			$values['email'] = $email;
			$values['created'] = date(DATE_ATOM);
			$values['lastlogin'] = date(DATE_ATOM);
			$values['password'] = password_hash( $password, PASSWORD_BCRYPT );
			$this->setuservalue(BASEYAML_APP_KEY, null, $values );
			$this->saveuserfile($username);
			return $this->loaduserfile($username);
		}
		else{
			return $this->login($username, $password);
		}
	}
	/**
	 * This function saves the user data to disk. 
	 * 
	 * @param  string $username Optional username: if absent, will get from the data
	 * @return boolean          True if properly saved.
	 */
	public function saveuserfile($username = null){
		if ( $username == null){
			$username = $this->getuservalue(BASEYAML_APP_KEY, 'username');
		}
		$filename = $this->GetUserFileName($username);
		return yaml_emit_file($filename, $this->userdata);
	}
	/**
	 * This function loads the user data from disk
	 * @param  string $username Optional username: if absent, will get from the data
	 * @return boolean          True is properly loaded
	 */
	public function loaduserfile($username = null){
		if ( $username == null){
			$username = $this->getuservalue(BASEYAML_APP_KEY, 'username');
		}
		$filename = $this->GetUserFileName($username);
		if ( is_file($filename)){
			$this->userdata =yaml_parse_file($filename);
			return true;
		}
		else{
			return false;
		}

	}
	/**
	 * Logs a user in and loads its data. If it fails, the data in memory 
	 * will be erased.
	 * 
	 * 
	 * @param  string $username The user to authenticate
	 * @param  string $password The password to check
	 * @return boolean          True if the user was logged in.
	 */
	public function login($username, $password){
		if ($this->loaduserfile($username)){
			$hashedpassword = $this->getuservalue(BASEYAML_APP_KEY, 'password');
			if ( password_verify($password, $hashedpassword)){
				$this->setuservalue(BASEYAML_APP_KEY, 'lastlogin', date(DATE_ATOM));
				$this->saveuserfile();
				return true;
			}
			else{
				$this->userdata = array();
			}

		}
		return false;
	}
	/**
	 * Allows to get a user value. The key is your application key.
	 *
	 * If the second parameter is omitted, all of your variables are returned.
	 * @param  string $key      Your application key
	 * @param  string $variable The name of the variable to return.
	 * @return mixed           value of the variable, or all of your values.
	 */
	public function getuservalue($key, $variable = null){
		if ( $variable != null){
			return $this->userdata[$key][$variable];
		}
		else{
			return $this->userdata[$key];	
		}
	}
	/**
	 * Sets a user value. The key is your application key, but if the
	 * variable is NULL and the value an array, it will merge them
	 * to your data.
	 * 
	 * @param  string $key      Your application key
	 * @param  string $variable The name of the variable to set (or NULL for all)
	 * @param  mixed $value    the value to set
	 */
	public function setuservalue($key, $variable, $value ){
		if ( is_array( $value) && $variable == null ){
			if ( $this->userdata[$key] == null){
				$this->userdata[$key] = array();
			}
			$this->userdata[$key] = array_merge($this->userdata[$key], $value);
		}
		else{
			$this->userdata[$key][$variable] = $value;	
		}
	}
}


/**
 * Very basic usage example:
 */

/*
$baseyaml = new baseyaml();
if ( $baseyaml->createuser('mpf', 'mpfrenette@gmail.com', 'test1')){
	echo "created";	
}
if ( $baseyaml->login('mpf',  'test1') ){
	echo "Logged in";
}
else{
	echo "failed";	
}

*/

?>