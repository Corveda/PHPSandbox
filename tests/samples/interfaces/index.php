<?php

interface MyInterface {

  function getText();

}

class MyImplementor implements MyInterface {

  function getText(){
    echo 'ok';
  }

}

$foo = new MyImplementor();
echo $foo->getText();
