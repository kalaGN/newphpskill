  <?php
  public function getmethods($classname) {
        $ref = new ReflectionClass($classname);

        $consts = $ref->getConstants(); //返回所有常量名和值
        echo "----------------consts:---------------" . PHP_EOL;
        foreach ($consts as $key => $val) {
            echo "$key : $val" . "<br>";
        }

        $props = $ref->getDefaultProperties();  //返回类中所有属性
        echo "--------------------props:--------------" . PHP_EOL . PHP_EOL;
        foreach ($props as $key => $val) {
            echo "$key : $val" . "<br>";  //  属性名和属性值
        }

        $methods = $ref->getMethods();     //返回类中所有方法
        echo "-----------------methods:---------------". "<br>";
        foreach ($methods as $method) {
            echo $method->getName() . "<br>";
        }
    }
    ?>
