<?
/**
 * BaseUserStorage class by Martin-Pierre Frenette 
 * Published under the GPL licenses.
 * 
 * Website: www.mpfrenette.com
 * Email: mpfrenette@gmail.com
 * Phone number: GET-YOU-DATA (438-968-3282)
 * Toll Free: 1-877-FRENETTE (373-6388)
 * 
 * Created in a step under 2 hours for the 2 hour code Challenge: www.2hourcode.com,
 * 
 * This class is an abstract base user storage class from which other classes can derive
 * to store user data.
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
 * If you modify values, make sure to call "saveuserdata" to commit to disk.
 *
 * It's that simple, but it was written in less than 2 hours on top of a 2 hours project!
 */
abstract class baseuserstorage{


	// this contains the userdata when a user is logged in
	var $userdata = array();

	/**
	 * This either creates the user with the username paramete, or, if the user
	 * already exists and the password matches, logs the user in.
	 *
	 * The email is also needed, but it's not really validated. Of course, the username
	 * can also be an email address.
	 *
	 * The password is to be encrypted with the password_hash function of PHP which is supposed
	 * to be secure. 
	 * 
	 * @param string $username the username which will be used to login. Can be an email
	 * @param string $email    the email address of the user
	 * @param string $password the password to login
	 * @return boolean : true if the user is created or logged in.
	 */
	abstract public  function CreateUser($username, $email, $password);

	/**
	 * This function saves the user data to the storage medium. 
	 * 
	 * @param  string $username Optional username: if absent, will get from the data
	 * @return boolean          True if properly saved.
	 */
	abstract public function SaveUserData($username = null);
	/**
	 * This function loads the user data from disk
	 * @param  string $username Optional username: if absent, will get from the data
	 * @return boolean          True is properly loaded
	 */
	abstract public function LoadUserData($username = null);
	
	/**
	 * Logs a user in and needs to load its data. If it fails, the data in memory 
	 * will be erased.
	 * 
	 * 
	 * @param  string $username The user to authenticate
	 * @param  string $password The password to check
	 * @return boolean          True if the user was logged in.
	 */
	abstract public function login($username, $password);
		
	/**
	 * Allows to get a user value. The key is your application key.
	 *
	 * If the second parameter is omitted, all of your variables are returned.
	 * @param  string $key      Your application key
	 * @param  string $variable The name of the variable to return.
	 * @return mixed           value of the variable, or all of your values.
	 */
	public function GetUserValue($key, $variable = null){
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
	public function SetUserValue($key, $variable, $value ){
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

?>