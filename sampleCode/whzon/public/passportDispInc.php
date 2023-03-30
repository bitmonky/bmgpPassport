<?php
$vmode = safeGET('mode');
$flex  = '100%';
$limit = 16;
$imgs  = null;

if($vmode == 'PC'){
  $flex = '20%';
  $limit = 20;
  $imgs = "style='height:80px;' ";
}
?>
<style>
* {
  box-sizing: border-box;
}

.header {
  text-align: center;
  padding: 32px;
}

.row {
  display: -ms-flexbox; /* IE10 */
  display: flex;
  -ms-flex-wrap: wrap; /* IE10 */
  flex-wrap: wrap;
  padding: 0 4px;
}

/* Create four equal columns that sits next to each other */
.column {
  -ms-flex: <?php echo $flex;?>; /* IE10 */
  flex: <?php echo $flex;?>;
  max-width: <?php echo $flex;?>;
  padding: 0 4px;
}

.column img {
  margin-top: 8px;
  background-color: black;
  border-radius:1.2em 1.2em 0em 0em;
  vertical-align: middle;
  width: 100%;
}
/* Responsive layout - makes a two column-layout instead of four columns */
@media screen and (max-width: 300px) {
  .column {
    -ms-flex: 50%;
    flex: 50%;
    max-width: 50%;
  }
}
</style>
<?php
//ini_set('display_errors',1);
//error_reporting(E_ALL);

$MyBrow = safeSRV('HTTP_USER_AGENT');

$sMode = safeGET('smode');
if ($sMode != null and $sMode !=''){
 $sMode = " and acCode = ".$sMode." ";
}

