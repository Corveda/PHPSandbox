<?php

trait MyTrait {

  function getText () {
    return 'ok';
  }

}

class MyClass {

  use MyTrait;

}

$foo = new MyClass();
echo $foo->getText();
