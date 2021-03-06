<?php

class GPNodeMap extends GPObject {

  private static $inverseMap = [];

  private static $map = [];

  private static function getMap() {
    if (!self::$map) {
      self::$map = @include ROOT_PATH.'maps/node';
    }
    return self::$map ?: [];
  }

  private static function getInverseMap() {
    if (!self::$inverseMap) {
      self::$inverseMap = array_flip(self::getMap());
    }
    return self::$inverseMap;
  }

    /**
     * getClass
     *
     * @param mixed $type Description.
     *
     * @access public
     * @static
     *
     * @return mixed Value.
     */
  public static function getClass($type) {
    if (!idx(self::getMap(), $type)) {
      self::regenMap();
    }
    return idxx(self::getMap(), $type);
  }

  public static function isNode($node_name) {
    $map = self::getInverseMap();
    if (!idx($map, $node_name)) {
      self::regenMap();
      $map = self::getInverseMap();
    }
    return array_key_exists($node_name, $map);
  }

  public static function regenAndGetAllTypes() {
    self::regenMap();
    return self::getMap();
  }

  public static function addToMapForTest($class) {
    GPEnv::assertTestEnv();
    self::$map[$class::getType()] = $class;
  }

  private static function regenMap() {
    self::$map = [];
    self::$inverseMap = [];
    $file = "<?php\nreturn [\n";
    foreach (GP::getModelsMap()->getAllFileNames() as $class) {
      $file .= '  '.$class::getType().' => \''.$class."',\n";
      self::$map[$class::getType()] = $class;
      self::$inverseMap[$class] = $class::getType();
    }
    $file .= "];\n";
    $file_path = ROOT_PATH.'maps/node';
    file_put_contents($file_path, $file);
    // TODO this is probably not safe
    @chmod(ROOT_PATH.'maps/node', 0666);
  }
}
