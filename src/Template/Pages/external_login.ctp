<?php

use App\View\AppView;
/**
 * @var $this AppView
 */

foreach($this->getVars() as $varName){
    dump($varName, $this->get($varName));
}

?>
