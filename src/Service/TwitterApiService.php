<?php

namespace App\Service;

use Abraham\TwitterOAuth\TwitterOAuth;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class TwitterApiService
{
    private $consumerKey;
    private $consumerSecret;
    private $accessToken;
    private $accessTokenSecret;

    public function __construct(ParameterBagInterface $getParams)
    {
        $this->consumerKey = $getParams->get('TWITTER_CONSUMER_KEY');
        $this->consumerSecret =  $getParams->get('TWITTER_CONSUMER_SECRET');
        $this->accessToken = $getParams->get('TWITTER_ACCESS_TOKEN');
        $this->accessTokenSecret = $getParams->get('TWITTER_ACCESS_TOKEN_SECRET');
    }

    public function checkTweet()
    {
        $connexion = new TwitterOAuth($this->consumerKey, $this->consumerSecret, $this->accessToken, $this->accessTokenSecret);
        $content = $connexion->get("account/verify_credentials");
        $botName = $content->screen_name;
        $statuses = $connexion->get("search/tweets", [
            'lang' => "fr",
            'q' => "pecho OR pécho -RT",
            'result_type' => "recent",
            'exclude_replies' => 'true',
        ]);
        $filename = 'public/tweetsReplied.csv';
        $file = fopen($filename, 'r+');
        foreach($statuses->statuses as $status) {
            if($status->user->screen_name === $botName){
                fwrite($file, $status->id.',');
                if($status->in_reply_to_status_id){
                    fwrite($file, $status->in_reply_to_status_id .',');
                }
                continue;
            }
            if($status->is_quote_status){
                continue;
            }
            $data = file($filename);
            if(!empty($data) && in_array($status->id, explode(',', $data[0]))){
                continue;
            }

            $this->reply($status->id, '@'.$status->user->screen_name);
            fwrite($file, $status->id.',');
            if($status->in_reply_to_status_id){
                fwrite($file, $status->in_reply_to_status_id .',');
            }
        }
        fclose($file);
    }

    public function reply(int $statusIdToReply, string $userToReply)
    {
        $content = ['status' => $userToReply . ' Pécho : du latin "pechus", "celui qui nique".'];
        $connexion = new TwitterOAuth($this->consumerKey, $this->consumerSecret, $this->accessToken, $this->accessTokenSecret);
        $connexion->post("statuses/update", ['in_reply_to_status_id'=> $statusIdToReply, "status" => $content]);
    }
}