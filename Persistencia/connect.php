<?php
class Conexion
{
  public function conectar()
  {
    $link = mysqli_connect("localhost", "root", "", "bdproyecto") or die("Error " . mysqli_error($link));
    return $link;
  }

  public function ejecutarSQL($sql)
  {
    $link = $this->conectar();
    $query = $sql or die("Error in the consult.." . mysqli_error($link));
    $result = mysqli_query($link, $query);
    return $result;
  }
}
?>