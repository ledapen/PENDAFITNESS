<?php
class NoSqlStats {
  private static function client(): ?Redis {
    if(!class_exists('Redis')) return null;
    $url=envv('REDIS_URL',''); if(!$url)return null;
    $parts=parse_url($url); if(!$parts||empty($parts['host']))return null;
    try{$r=new Redis();$scheme=$parts['scheme']??'redis';$host=($scheme==='rediss'?'tls://':'').$parts['host'];$r->connect($host,(int)($parts['port']??6379),2.0);if(isset($parts['pass'])){$user=$parts['user']??null;$r->auth($user?[$user,$parts['pass']]:$parts['pass']);}return $r;}catch(Throwable $e){error_log('Redis unavailable: '.$e->getMessage());return null;}
  }
  public static function recordActivityView(int $activityId): bool { $r=self::client();if(!$r)return false;try{$day=date('Y-m-d');$key="pendafitness:activity:$activityId:views:$day";$r->incr($key);$r->expire($key,60*60*24*31);$r->hSet('pendafitness:last_views',(string)$activityId,json_encode(['activity_id'=>$activityId,'viewed_at'=>date(DATE_ATOM)],JSON_UNESCAPED_SLASHES));return true;}catch(Throwable $e){error_log('Redis write failed: '.$e->getMessage());return false;} }
}
