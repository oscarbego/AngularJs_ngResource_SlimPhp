<?php

require 'Slim/Slim.php';

$app = new Slim();

$app->get('/wines', 'getWines');



$app->get('/particursos', 'getPartiCursos');
$app->get('/cursosxfecha', 'getCursosxFecha');
$app->get('/fechas', 'getFechas');

$app->get('/addpc', 'addpc');

$app->get('/wines/:id',	'getWine');
$app->get('/wines/search/:query', 'findByName');
$app->post('/wines', 'addWine');
$app->put('/wines/:id', 'updateWine');
$app->delete('/wines/:id',	'deleteWine');

$app->run();

function getPartiCursos() {

$sqlParti = "select *, ('false') as selected, ('') as cursos from participantes";
	
$sqlPartiCursos = <<<EOT
SELECT
  `participantes`.`id`,
  `cursos`.`nombre`,
  `cursos`.`descri`
FROM
  `cursos`
  INNER JOIN `participantes_cursos` ON `participantes_cursos`.`cursos_id` =
    `cursos`.`id`
  INNER JOIN `participantes` ON `participantes`.`id` =
    `participantes_cursos`.`participantes_id`
where `participantes`.`id` = 
EOT;


	try {
		$db = getConnection();
		
		$stmt = $db->query($sqlParti);  
		$participantes = $stmt->fetchAll(PDO::FETCH_OBJ);
		
		$i = 0;
		foreach ($participantes as $k) {
    		//echo $participantes[$i]->nombre . " ";

    		//echo $participantes[$i]->selected ." ";

    		
    		$stmt = $db->query($sqlPartiCursos . $participantes[$i]->id);  
			$partiCursos = $stmt->fetchAll(PDO::FETCH_OBJ);
					
    		$participantes[$i]->cursos = $partiCursos;// json_encode($partiCursos);
    		

    		//echo $participantes[$i]->cursos ." - ";

    		$i++;

    		//echo "<hr>";
		}
		
		$db = null;

		echo json_encode($participantes);
	} catch(PDOException $e) {
		echo '{"error":{"text":'. $e->getMessage() .'}}'; 
	}
}


function getCursosxFecha() {

$sqlCursosxFecha = <<<EOT
SELECT
  `cursos`.`id`,
  `cursos`.`nombre`,
  `cursos`.`descri`,
  count(*) AS `np`,
  `fechas`.`fecha`,
  ('false') as selected
FROM
  `cursos`
  INNER JOIN `participantes_cursos` ON `participantes_cursos`.`cursos_id` =
    `cursos`.`id`
  INNER JOIN `fechas` ON `cursos`.`fecha_id` = `fechas`.`Id`
GROUP BY
  `cursos`.`id`,
  `cursos`.`nombre`,
  `cursos`.`descri`,
  `fechas`.`fecha`; 
EOT;

	try {
		$db = getConnection();
		
		$stmt = $db->query($sqlCursosxFecha);  
		$cursosxFecha = $stmt->fetchAll(PDO::FETCH_OBJ);
		
		$db = null;

		echo json_encode($cursosxFecha);
	} catch(PDOException $e) {
		echo '{"error":{"text":'. $e->getMessage() .'}}'; 
	}
}


function getFechas() {

$sqlFechas = "SELECT * FROM fechas;";

	try {
		$db = getConnection();
		
		$stmt = $db->query($sqlFechas);  
		$fechas = $stmt->fetchAll(PDO::FETCH_OBJ);
		
		$db = null;

		echo json_encode($fechas);
	} catch(PDOException $e) {
		echo '{"error":{"text":'. $e->getMessage() .'}}'; 
	}
}

function getWines() {
	$sql = "select * FROM wine ORDER BY name";
	try {
		$db = getConnection();
		$stmt = $db->query($sql);  
		$wines = $stmt->fetchAll(PDO::FETCH_OBJ);
		$db = null;
		echo '{"wine": ' . json_encode($wines) . '}';
	} catch(PDOException $e) {
		echo '{"error":{"text":'. $e->getMessage() .'}}'; 
	}
}


function getWine($id) {
	$sql = "SELECT * FROM wine WHERE id=:id";
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);  
		$stmt->bindParam("id", $id);
		$stmt->execute();
		$wine = $stmt->fetchObject();  
		$db = null;
		echo json_encode($wine); 
	} catch(PDOException $e) {
		echo '{"error":{"text":'. $e->getMessage() .'}}'; 
	}
}


function addpc() {
	//$sql = "insert into participantes_cursos values (:pid, :cid)";

	//try {

		/*$db = getConnection();
		$stmt = $db->prepare($sql);  
		
		$stmt->bindParam("pid", $pid);
		//$stmt->bindParam("Cid", $cid);
		
		$stmt->execute();
		$db = null;
		*/
		//echo $sql;
		echo '{"save":true}'; 
	//} 
	//catch(PDOException $e) {
	//	echo '{"save":false}'; 
	//}
}




function addWine() {
	//error_log('addWine\n', 3, '/var/tmp/php.log');
	$request = Slim::getInstance()->request();
	$wine = json_decode($request->getBody());
	$sql = "INSERT INTO wine (name, grapes, country, region, year, description) VALUES (:name, :grapes, :country, :region, :year, :description)";
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);  
		$stmt->bindParam("name", $wine->name);
		$stmt->bindParam("grapes", $wine->grapes);
		$stmt->bindParam("country", $wine->country);
		$stmt->bindParam("region", $wine->region);
		$stmt->bindParam("year", $wine->year);
		$stmt->bindParam("description", $wine->description);
		$stmt->execute();
		$wine->id = $db->lastInsertId();
		$db = null;
		echo json_encode($wine); 
	} catch(PDOException $e) {
		error_log($e->getMessage(), 3, '/var/tmp/php.log');
		echo '{"error":{"text":'. $e->getMessage() .'}}'; 
	}
}

function updateWine($id) {
	$request = Slim::getInstance()->request();
	$body = $request->getBody();
	$wine = json_decode($body);
	$sql = "UPDATE wine SET name=:name, grapes=:grapes, country=:country, region=:region, year=:year, description=:description WHERE id=:id";
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);  
		$stmt->bindParam("name", $wine->name);
		$stmt->bindParam("grapes", $wine->grapes);
		$stmt->bindParam("country", $wine->country);
		$stmt->bindParam("region", $wine->region);
		$stmt->bindParam("year", $wine->year);
		$stmt->bindParam("description", $wine->description);
		$stmt->bindParam("id", $id);
		$stmt->execute();
		$db = null;
		echo json_encode($wine); 
	} catch(PDOException $e) {
		echo '{"error":{"text":'. $e->getMessage() .'}}'; 
	}
}

function deleteWine($id) {
	$sql = "DELETE FROM wine WHERE id=:id";
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);  
		$stmt->bindParam("id", $id);
		$stmt->execute();
		$db = null;
	} catch(PDOException $e) {
		echo '{"error":{"text":'. $e->getMessage() .'}}'; 
	}
}

function findByName($query) {
	$sql = "SELECT * FROM wine WHERE UPPER(name) LIKE :query ORDER BY name";
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$query = "%".$query."%";  
		$stmt->bindParam("query", $query);
		$stmt->execute();
		$wines = $stmt->fetchAll(PDO::FETCH_OBJ);
		$db = null;
		echo '{"wine": ' . json_encode($wines) . '}';
	} catch(PDOException $e) {
		echo '{"error":{"text":'. $e->getMessage() .'}}'; 
	}
}

function getConnection() {
	$dbhost="127.0.0.1";
	$dbuser="startupw_prueba";
	$dbpass="edi2234.";
	$dbname="startupw_prueba";
	$dbh = new PDO("mysql:host=$dbhost;dbname=$dbname", $dbuser, $dbpass);	
	$dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
	return $dbh;
}

?>