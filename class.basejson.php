<?
/**
 * Basejson class by Martin-Pierre Frenette 
 * Published under the GPL licenses.
 * 
 * Website: www.mpfrenette.com
 * Email: mpfrenette@gmail.com
 * Phone number: GET-YOU-DATA (438-968-3282)
 * Toll Free: 1-877-FRENETTE (373-6388)
 * 
 * Created in under 2 hours for the 2 hour code Challenge: www.2hourcode.com
 *
 * This class is a very, very basic user storage class which saves in a json
 * file user data.
 *
 * It needs the file class.basejson.config.php which only has 2 constants:
 * BASEJSON_STORAGE_DIRECTORY : which indicates in which non-web accessible directory to place your files
 * BASEJSON_CONFIG_FILE: which indicates the file in that directory to store the default config in.
 *
 * The system is rather simple: you simply create an instance of the class and call the public functions.
 * 
 * The CreateUser function will either create a new user or login as that user if the username and password matches.
 * The LoginUser function will try login.
 *
 * Both will automatically call LoddUserData.
 *
 * You can use getuservalue to query the storage, the key is your own application key, thus
 * allowing multiple systems to use the same data files.
 *
 * You can use setuservalue to set values in storage.
 *
 * If you modify values, make sure to call "saveuserdata" to commit to disk.
 *
 * It's that simple, but it was written in less than 2 hours... n fact, it was made in 22 minutes.
 */

require_once( 'class.baseuserstorage.php');

define(  "BASEJSON_APP_KEY", '_basejson');

class basejson extends baseuserstorage{

	// this is the class config itself
	var $config = array();

	
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

		
		if ( is_file('class.basejson.config.php')){
			require_once('class.basejson.config.php');

			if ( !is_dir(BASEJSON_STORAGE_DIRECTORY) && !mkdir(BASEJSON_STORAGE_DIRECTORY) ){
				echo 'Error: class.basejson.php is not able to create the directory:'. BASEJSON_STORAGE_DIRECTORY;
				exit();		
			
			}
			if ( !is_file(BASEJSON_STORAGE_DIRECTORY. BASEJSON_CONFIG_FILE)){
				$this->CreateDefaultConfig();
			}
			$this->LoadConfig();
			
		}
		else{
			echo 'Error: class.basejson.config.php is not found';
			exit();
		}


	}

	/**
	 * This function will take the array, encode it to json and save it to disk.
	 * @param string $filename the file name to save to
	 * @param array $array    the data to save
	 * @return int 			  the return from the file_put_contents function 
	 */
	protected function WriteJsonToFile($filename, $array){

		$filecontent = json_encode($array);
		return (file_put_contents ($filename, $filecontent) >0);


	}

	/**
	 * This function will read and parse json content from the 
	 * file and return it as an array.
	 * 
	 * @param [type] $filename  the file to read
	 * @return array 			the content of the file, parse into an array
	 */
	protected function ReadJsonFromFile($filename){

		$filecontent = file_get_contents($filename);
		return json_decode($filecontent, true);

	}

	/**
	 * At this moment, the config only stores a random seed.
	 */
	protected function CreateDefaultConfig(){
		
		$values= array();

		// for this purpose, we only need a random seed, so sha1 is enough.
		$values[BASEJSON_APP_KEY]['seed'] = sha1(openssl_random_pseudo_bytes(16));

		if ( !$this->WriteJsonToFile(BASEJSON_STORAGE_DIRECTORY. BASEJSON_CONFIG_FILE, $values) ){
			echo 'Error: unable to create the '. BASEJSON_STORAGE_DIRECTORY. BASEJSON_CONFIG_FILE. ' file. ';
			exit();

		}
	}
	/**
	 * This function, called in the constructor, simply loads the general config
	 */
	protected function LoadConfig(){
		$this->config = $this->ReadJsonFromFile(BASEJSON_STORAGE_DIRECTORY. BASEJSON_CONFIG_FILE);
	}

	/**
	 * The userdata filename is stored using the sha1 function, which is NOT secured, but it's enough
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
		return  BASEJSON_STORAGE_DIRECTORY. sha1($username . $this->config[BASEJSON_APP_KEY]['seed']). '.json';
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
			$this->setuservalue(BASEJSON_APP_KEY, null, $values );
			$this->saveuserdata($username);
			return $this->loaduserdata($username);
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
	public function SaveUserData($username = null){
		if ( $username == null){
			$username = $this->getuservalue(BASEJSON_APP_KEY, 'username');
		}
		$filename = $this->GetUserFileName($username);
		return $this->WriteJsonToFile($filename, $this->userdata);
	}
	/**
	 * This function loads the user data from disk
	 * @param  string $username Optional username: if absent, will get from the data
	 * @return boolean          True is properly loaded
	 */
	public function LoadUserData($username = null){
		if ( $username == null){
			$username = $this->getuservalue(BASEJSON_APP_KEY, 'username');
		}
		$filename = $this->GetUserFileName($username);

		if ( is_file($filename)){
			$this->userdata =$this->ReadJsonFromFile($filename);
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
		if ($this->loaduserdata($username)){
			$hashedpassword = $this->getuservalue(BASEJSON_APP_KEY, 'password');
			if ( password_verify($password, $hashedpassword)){
				$this->setuservalue(BASEJSON_APP_KEY, 'lastlogin', date(DATE_ATOM));
				$this->saveuserdata();
				return true;
			}
			else{
				$this->userdata = array();
			}

		}
		return false;
	}
}


/**
 * Very basic usage example:
 */

/*
$baseJSON = new baseJSON();
if ( $baseJSON->createuser('mpf', 'mpfrenette@gmail.com', 'test1')){
	echo "created";	
}
if ( $baseJSON->login('mpf',  'test1') ){
	echo "Logged in";
}
else{
	echo "failed";	
}

*/

?>