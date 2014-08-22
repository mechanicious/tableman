<?php

/*
  |--------------------------------------------------------------------------
  | Extension Register
  |--------------------------------------------------------------------------
  |
  | Tableman uses so called lazy loading for classes. Which means the classes
  | are only loaded when they're needed. To acquire it, Tableman will look for
  | a class in this register and then either decide to load the class if the
  | class is found.
  */


return $register = array(
  // 1. Classname, e.g. Superman. Which will be loaded if Tableman::superman() is called and
  // if Tableman::superman is not a standard method. 
  // 2. The register entry key has to be the same as the classname!
  'Bs3Table' => array(
    // E.g. for the class Superman, it could be: mechanicious\Superman\Superman
    'fully_qualified_classname' => 'mechanicious\Extensions\Bs3Table\Bs3Table',
    'relative_path' => '/../Extensions/Bs3Table/Bs3Table.php',
    )
  );