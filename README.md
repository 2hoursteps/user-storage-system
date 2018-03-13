Baseyaml class by Martin-Pierre Frenette 
Published under the GPL licenses.

Website: www.mpfrenette.com
Email: mpfrenette@gmail.com
Phone number: GET-YOU-DATA (438-968-3282)
Toll Free: 1-877-FRENETTE (373-6388)

Created in under 2 hours for the 2 hour code Challenge: www.2hourcode.com
This class is a very, very basic user storage class which saves in a yaml
file user data.
It needs the yaml PECL library installed in PHP.
It needs the file class.baseyaml.config.php which only has 2 constants:
BASEYAML_STORAGE_DIRECTORY : which indicates in which non-web accessible directory to place your files
BASEYAML_CONFIG_FILE: which indicates the file in that directory to store the default config in.
The system is rather simple: you simply create an instance of the class and call the public functions.

The CreateUser function will either create a new user or login as that user if the username and password matches.
The LoginUser function will try login.
Both will automatically call LoddUserFile.
You can use getuservalue to query the storage, the key is your own application key, thus
allowing multiple systems to use the same data files.
You can use setuservalue to set values in storage.
If you modify values, make sure to call "saveuserfile" to commit to disk.
It's that simple, but it was written in less than 2 hours... 
