<?php
class Auth {			//Regisztráció, Bejelentkeztetés, Jogosultságok kezelése, Kijelentkeztetés
  private $user_storage;
  private $user = NULL;

  public function __construct(IStorage $user_storage) {										                  //A konstruktornak egy már létrehozott (User)Storage-et kell átadni.
    $this->user_storage = $user_storage;

    if (isset($_SESSION["user"])) {
      $this->user = $_SESSION["user"];
    }
  }

  /*public function user_exists($username) {													                        //Létezik-e már a megadott felhasználó? (regisztrációhoz)
    $users = $this->user_storage->findOne(['username' => $username]);
    return !is_null($users);
  }*/

  public function user_exists($email_address) {													                        //Létezik-e már a megadott felhasználó? (regisztrációhoz)
    $users = $this->user_storage->findOne(['email_address' => $email_address]);
    return !is_null($users);
  }

  /*public function register($data) {															                            //Regisztráljuk a megadott felhasználót (új felhasználó felvétele).
    $user = [
      'username'  => $data['username'],
      'password'  => password_hash($data['password'], PASSWORD_DEFAULT),
      'fullname'  => $data['fullname'],
      "roles"     => ["user"],
    ];
    return $this->user_storage->add($user);
  }*/

  public function register_user($data) {															                            //Módosítva a jelenlegi feladathoz.
    $user = [
      'fullname'  => $data['fullname'],
      'TAJ_number' => $data['TAJ_number'],
      'home_address' => $data['home_address'],
      'email_address' => $data['email_address'],
      'password'  => password_hash($data['password'], PASSWORD_DEFAULT),
      'appointment' => "none",
      "roles"     => ["user"]
    ];
    return $this->user_storage->add($user);
  }

  public function register_admin($data) {															                            //Módosítva a jelenlegi feladathoz.
    $user = [
      'fullname'  => $data['fullname'],
      'TAJ_number' => $data['TAJ_number'],
      'home_address' => $data['home_address'],
      'email_address' => $data['email_address'],
      'password'  => password_hash($data['password'], PASSWORD_DEFAULT),
      'appointment' => "none",
      "roles"     => ["user", "admin"]
    ];
    return $this->user_storage->add($user);
  }

  public function authenticate($username, $password) {										                  //Van-e már ilyen regisztrált felhasználó? (bejelentkeztetéshez)
    $users = $this->user_storage->findMany(function ($user) use ($username, $password) {
      return $user["username"] === $username && 
             password_verify($password, $user["password"]);
    });
    return count($users) === 1 ? array_shift($users) : NULL;
  }

  public function login($user) {							        //Megadott felhasználó bejelentkeztetése.
    $this->user = $user;									            //Felhasználói munkamenet indítása.
    $_SESSION["user"] = $user;
  }

  public function is_authenticated() {					    	//Be van-e éppen jelentkezve felhasználó?
    return !is_null($this->user);
  }

  public function authenticated_user() {				    	//A jelenlegi felhasználó lekérdezése.
    return $this->user;
  }

  public function authorize($roles = []) {					//Vannak-e ilyen jogosultságai az adott felhasználónak?
    if (!$this->is_authenticated()) {						      //Ha nincs bejelentkezve felhasználó, hamisat ad vissza.
      return FALSE;
    }
    foreach ($roles as $role) {
      if (in_array($role, $this->user["roles"])) {
        return TRUE;
      }
    }
    return FALSE;
  }

  public function logout() {								          //Jelenlegi felhasználó kijelentkeztetése.
    $this->user = NULL;
    unset($_SESSION["user"]);								          //Felhasználói munkamenet befejezése.
  }

  //Megjegyzés: Mi lenne, ha a login() metódusban lenne a session_start(), a logout()-ban pedig a session_destroy()?
}
?>