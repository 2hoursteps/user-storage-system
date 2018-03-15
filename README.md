BaseUserStorage, baseyaml and basejson class by Martin-Pierre Frenette 
Published under the GPL licenses.

Website: www.mpfrenette.com
Email: mpfrenette@gmail.com
Phone number: GET-YOU-DATA (438-968-3282)
Toll Free: 1-877-FRENETTE (373-6388)

Created in under two 2 hours for the 2 hour code Challenge: www.2hourcode.com

This system is a very, very basic user storage class which saves either in a yaml or json file user data.

BaseYaml It needs the yaml PECL library installed in PHP, but the basejson should work easily.

It needs the file class.base####.config.php (where #### is wither yaml or json) which only has 2 constants:

BASE####_STORAGE_DIRECTORY : which indicates in which non-web accessible directory to place your files
BASE####_CONFIG_FILE: which indicates the file in that directory to store the default config in.

The system is rather simple: you simply create an instance of the class and call the public functions.

The CreateUser function will either create a new user or login as that user if the username and password matches.

The LoginUser function will try login.

Both will automatically call LoddUserData

You can use getuservalue to query the storage, the key is your own application key, thus allowing multiple systems to use the same data files.

You can use setuservalue to set values in storage.

If you modify values, make sure to call "saveuserdata" to commit to disk.

It's that simple, but it was written in less than 2 hours for the baseyaml, and in 22 minutes for the other 2 classes (including changes in baseyaml to fit in the new structure.

More classes will be added to support other features in other 2 hours steps.
