<?php
session_start();require __DIR__.'/../app/config/config.php';
foreach(['HomeController','ActivityController','AuthController','UserController','CoachController','AdminController'] as $c)require __DIR__.'/../app/controllers/'.$c.'.php';
$path=parse_url($_SERVER['REQUEST_URI'],PHP_URL_PATH);$method=$_SERVER['REQUEST_METHOD'];
$routes=[
['GET','/',HomeController::class,'index'],['GET','/activites',ActivityController::class,'index'],['GET','/contact',HomeController::class,'contact'],['POST','/contact',HomeController::class,'contact'],
['GET','/login',AuthController::class,'login'],['POST','/login',AuthController::class,'login'],['GET','/register',AuthController::class,'register'],['POST','/register',AuthController::class,'register'],['GET','/logout',AuthController::class,'logout'],
['GET','/mes-reservations',UserController::class,'reservations'],['GET','/profil',UserController::class,'profile'],['POST','/profil',UserController::class,'profile'],['GET','/favoris',UserController::class,'favorites'],['POST','/favori',UserController::class,'favorite'],['POST','/reserver',UserController::class,'book'],['POST','/annuler',UserController::class,'cancel'],['POST','/avis',UserController::class,'review'],
['GET','/coach',CoachController::class,'dashboard'],['GET','/coach/activites',CoachController::class,'activities'],['POST','/coach/activites/save',CoachController::class,'saveActivity'],['POST','/coach/activites/toggle',CoachController::class,'toggleActivity'],['GET','/coach/seances',CoachController::class,'sessions'],
['POST','/coach/seances/add',CoachController::class,'addSession'],
['POST','/coach/seances/delete',CoachController::class,'deleteSession'],

['POST','/coach/seances/recurrentes/add',CoachController::class,'addSchedule'],
['POST','/coach/seances/recurrentes/pause',CoachController::class,'pauseSchedule'],
['POST','/coach/seances/recurrentes/resume',CoachController::class,'resumeSchedule'],

['POST','/coach/seances/block',CoachController::class,'blockSession'],
['POST','/coach/seances/reopen',CoachController::class,'reopenSession'],['GET','/coach/reservations',CoachController::class,'reservations'],['POST','/coach/reservations/status',CoachController::class,'reservationStatus'],['GET','/coach/avis',CoachController::class,'reviews'],['POST','/coach/avis/status',CoachController::class,'reviewStatus'],['GET','/coach/contacts',CoachController::class,'contacts'],['POST','/coach/contacts/processed',CoachController::class,'contactProcessed'],
['GET','/admin',AdminController::class,'dashboard'],['GET','/admin/equipe',AdminController::class,'staff'],['POST','/admin/equipe/create',AdminController::class,'createCoach'],['POST','/admin/equipe/toggle',AdminController::class,'toggleCoach']];
if(preg_match('#^/api/activite/(\\d+)/seances$#',$path,$m)&&$method==='GET'){(new ActivityController)->sessionsJson((int)$m[1]);exit;}
if(preg_match('#^/activite/(\\d+)$#',$path,$m)){(new ActivityController)->show((int)$m[1]);exit;}
foreach($routes as [$verb,$url,$cls,$fn])if($method===$verb&&$path===$url){(new $cls)->$fn();exit;}
http_response_code(404);view('errors/404');
