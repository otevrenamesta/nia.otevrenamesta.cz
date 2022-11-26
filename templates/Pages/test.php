<?php

use App\View\AppView;
/**
 * @var $this AppView
 */

echo $this->Html->link($link);

foreach($this->getVars() as $varName){
    dump($varName, $this->get($varName));
}

?>
