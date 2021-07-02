<?php

interface IFileIO {
  function save($data);
  function load();
}
abstract class FileIO implements IFileIO {
  protected $filepath;

  public function __construct($filename) {
    if (!is_readable($filename) || !is_writable($filename)) {
      throw new Exception("Data source ${filename} is invalid.");
    }
    $this->filepath = realpath($filename);
  }
}
class JsonIO extends FileIO {                                 //Egy ilyennel inicializáld a Storage objektumaidat.
  public function load($assoc = true) {
    $file_content = file_get_contents($this->filepath);
    return json_decode($file_content, $assoc) ?: [];
  }

  public function save($data) {
    $json_content = json_encode($data, JSON_PRETTY_PRINT);
    file_put_contents($this->filepath, $json_content);
  }
}
class SerializeIO extends FileIO {
  public function load() {
    $file_content = file_get_contents($this->filepath);
    return unserialize($file_content) ?: [];
  }

  public function save($data) {
    $serialized_content = serialize($data);
    file_put_contents($this->filepath, $serialized_content);
  }
}

interface IStorage {
  function add($record): string;                        //Csak a hozzáadandó rekorodot kell átadnod neki, json ID majd automatikusan generálódik neki.
  function findById(string $id);
  function findAll(array $params = []);                 //A this->contents-et nem kérheted le közvetlenül, de ezzel kilistázhatod az összes elemet.
  function findOne(array $params = []);                 //Például belső id alapján keresésre.
  function update(string $id, $record);                 //Első paraméter a frissítendő objektum ['id'] mezője, a második pedig maga a frissítendő objektum.
  function delete(string $id);

  function findMany(callable $condition);               //A megadott egyváltozós függvény mentén kiválogatja a contents tömb megfelelő elemeit.
  function updateMany(callable $condition, callable $updater);
  function deleteMany(callable $condition);             //A megadott egyváltozós függvény mentén kifilterezi a contents tömböt.
}

class Storage implements IStorage {
  protected $contents;
  protected $io;

  public function __construct(IFileIO $io, $assoc = true) {     //Egy JsonIO-val inicializáld. Bizonyosodj meg róla, hogy a fájl írható és olvasható (WinSCP file properties)
    $this->io = $io;
    $this->contents = (array)$this->io->load($assoc);
  }

  public function __destruct() {                                //Ez automatikusan meghívódik a PHP script lefutása végén.
    $this->io->save($this->contents);
  }

  public function add($record): string {
    $id = uniqid();                       //így generálódik az id-mező
    if (is_array($record)) {
      $record['id'] = $id;
    }
    else if (is_object($record)) {
      $record->id = $id;
    }
    $this->contents[$id] = $record;
    return $id;
  }

  public function findById(string $id) {
    return $this->contents[$id] ?? NULL;
  }

  public function findAll(array $params = []) {
    return array_filter($this->contents, function ($item) use ($params) {
      foreach ($params as $key => $value) {
        if (((array)$item)[$key] !== $value) {
          return FALSE;
        }
      }
      return TRUE;
    });
  }

  public function findOne(array $params = []) {
    $found_items = $this->findAll($params);
    $first_index = array_keys($found_items)[0] ?? NULL;
    return $found_items[$first_index] ?? NULL;
  }

  public function update(string $id, $record) {
    $this->contents[$id] = $record;
  }

  public function delete(string $id) {
    unset($this->contents[$id]);
  }

  public function findMany(callable $condition) {
    return array_filter($this->contents, $condition);
  }

  public function updateMany(callable $condition, callable $updater) {
    array_walk($all, function (&$item) use ($condition, $updater) {
      if ($condition($item)) {
        $updater($item);
      }
    });
  }

  public function deleteMany(callable $condition) {
    $this->contents = array_filter($this->contents, function ($item) use ($condition) {
      return !$condition($item);
    });
  }
}