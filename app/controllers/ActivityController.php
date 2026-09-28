<?php
require_once __DIR__.'/../models/Activity.php';
require_once __DIR__.'/../models/Review.php';
require_once __DIR__.'/../models/NoSqlStats.php';

class ActivityController {
  public function index(){
    $activities=Activity::all($_GET);
    $categories=Activity::categories();
    view('activities/index',compact('activities','categories'));
  }
  public function show($id){
    $activity=Activity::find($id);
    if(!$activity){http_response_code(404);die('Activité introuvable');}
    $sessions=Activity::sessions($id);
    $reviews=Review::approved($id);
    NoSqlStats::recordActivityView((int)$id);
    view('activities/show',compact('activity','sessions','reviews'));
  }
  public function sessionsJson($id){
    $activity=Activity::find($id);
    if(!$activity){http_response_code(404);header('Content-Type: application/json; charset=utf-8');echo json_encode(['error'=>'Activité introuvable']);return;}
    header('Content-Type: application/json; charset=utf-8');
    header('Cache-Control: no-store');
    echo json_encode(['activity_id'=>(int)$id,'sessions'=>Activity::sessions($id)],JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES);
  }
}
