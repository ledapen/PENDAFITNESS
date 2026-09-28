<?php
function envv($k,$d=null){$v=getenv($k);return $v===false?$d:$v;}
function db(): PDO {
  static $pdo;
  if ($pdo) return $pdo;
  $host=envv('DB_HOST','127.0.0.1'); $port=envv('DB_PORT','3306'); $name=envv('DB_NAME','urbanfit');
  $dsn="mysql:host=$host;port=$port;dbname=$name;charset=utf8mb4";
  $opt=[PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION,PDO::ATTR_DEFAULT_FETCH_MODE=>PDO::FETCH_ASSOC];
  if(envv('DB_SSL','false')==='true'){$opt[PDO::MYSQL_ATTR_SSL_CA]='/etc/ssl/certs/ca-certificates.crt';$opt[PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT]=true;}
  $pdo=new PDO($dsn,envv('DB_USER','root'),envv('DB_PASS',''),$opt); return $pdo;
}
function e($v){return htmlspecialchars((string)$v,ENT_QUOTES,'UTF-8');}
function csrf(){if(empty($_SESSION['csrf']))$_SESSION['csrf']=bin2hex(random_bytes(32));return $_SESSION['csrf'];}
function check_csrf(){if(!hash_equals($_SESSION['csrf']??'',$_POST['csrf']??'')){http_response_code(419);die('Jeton CSRF invalide');}}
function auth(){return $_SESSION['user']??null;} function requireAuth(){if(!auth()){flash('warning','Connectez-vous pour continuer.');header('Location:/login');exit;}}
function requireRole($r){requireAuth();if((auth()['role']??'')!==$r){http_response_code(403);die('Accès refusé');}}
function flash($type,$message){$_SESSION['flash'][]=['type'=>$type,'message'=>$message];}
function flashes(){$x=$_SESSION['flash']??[];unset($_SESSION['flash']);return $x;}
function redirect($url){header('Location:'.$url);exit;}
function view($name,$data=[]){extract($data);require __DIR__.'/../views/layouts/header.php';require __DIR__.'/../views/'.$name.'.php';require __DIR__.'/../views/layouts/footer.php';}
