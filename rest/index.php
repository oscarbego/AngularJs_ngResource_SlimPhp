<?php

require 'Slim/Slim.php';

$app = new Slim();

$app->get('/', 'index');
$app->get('/projects', 'getProjects');
$app->get('/projects/:id', 'getProject');
$app->post('/projects', 'addProject');
$app->put ('/projects', 'updateProject');
$app->delete('/projects/:id', 'delProject');

$app->run();


function index() {
	
$request = <<<EOT
	<h1>index<h1/> 
EOT;

	echo $request;
}


function getProjects() {
	$sql = "select * from projects";
	
	try {
		$db = getConnection();
		$stmt = $db->query($sql);  
		$projects = $stmt->fetchAll(PDO::FETCH_OBJ);
		$db = null;
		echo json_encode($projects);
	} catch(PDOException $e) {
		echo '{"error":{"text":'. $e->getMessage() .'}}'; 
	}
	
}


function getProject($id) {
	$sql = "select * from projects WHERE id=:id;";

	try {
		$db = getConnection();		
		$stmt = $db->prepare($sql);  
		$stmt->bindParam("id", $id);
		$stmt->execute();
		$project = $stmt->fetchObject();

		$db = null;
		
		echo json_encode($project);

	} catch(PDOException $e) {
		echo '{"error":{"text":'. $e->getMessage() .'}}'; 
	}
}


function addProject() {
	$request = Slim::getInstance()->request();
	$project = json_decode($request->getBody());
	$sql = "INSERT INTO projects (name, phone) VALUES (:name, :phone)";

	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);  
		$stmt->bindParam("name", $project->name);
		$stmt->bindParam("phone", $project->phone);
		
		$stmt->execute();
		$project->id = $db->lastInsertId();
		$db = null;
		
		echo json_encode($project); 

	} catch(PDOException $e) {
		echo '{"error":{"text":'. $e->getMessage() .'}}'; 
	}
}


function updateProject() {
	$request = Slim::getInstance()->request();
	$body = $request->getBody();
	$project = json_decode($body);

	$sql = "UPDATE projects SET name=:name, phone=:phone WHERE id=:id";

	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);  
		$stmt->bindParam("name", $project->name);
		$stmt->bindParam("phone", $project->phone);
		
		$stmt->bindParam("id", $project->id);
		$stmt->execute();
		$db = null;
		echo json_encode($project); 
	} catch(PDOException $e) {
		echo '{"error":{"text":'. $e->getMessage() .'}}'; 
	}
}


function delProject($id) {
	$sql = "DELETE FROM projects WHERE id=:id";

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


function getConnection() {
	$dbhost="127.0.0.1";
	
	$dbuser="root";
	$dbpass="root";

	$dbname="db_prueba";
	$dbh = new PDO("mysql:host=$dbhost;dbname=$dbname", $dbuser, $dbpass);	
	$dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
	return $dbh;
}

?>