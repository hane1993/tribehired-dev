<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ApiController extends Controller
{

    private $url = 'https://jsonplaceholder.typicode.com/';

    public function getComments()
    {
        $apiResponse = $this->sendApiRequest('comments');

        return response()->json($apiResponse);
    }

    public function getPost($id)
    {
        $apiResponse = $this->sendApiRequest('posts/' . $id);

        $response = [
            'post_id' => $apiResponse->id,
            'post_title' => $apiResponse->title,
            'post_body' => $apiResponse->body,
        ];

        return response()->json($response);
    }

    public function getAllPosts()
    {
        $posts = $this->sendApiRequest('posts');

        $commentPostCounts = $this->getCommentsByCount();

        uksort($posts, function($key1, $key2) use ($commentPostCounts) {
            return (array_search($key1, $commentPostCounts) > array_search($key2, $commentPostCounts));
        });

        foreach ($posts as $post) {
            $response[] = [
                'post_id' => $post->id,
                'post_title' => $post->title,
                'post_body' => $post->body,
                'total_number_of_comments' => $commentPostCounts[$post->id],
            ];
        }

        return response()->json($response);
    }

    public function getCommentSearch(Request $request)
    {
        $totalParameters = count($request->all());
        $id = $request->has('id') ? $request->id : '';
        $postId = $request->has('postId') ? $request->postId :'';
        $name = $request->has('name') ? $request->name : '';
        $email = $request->has('email') ? $request->email : '';
        $body = $request->has('body') ? $request->body : '';

        $result = [];

        $apiResponse = json_decode(json_encode($this->sendApiRequest('comments')), true);

        foreach ($apiResponse as $comment) {
            $count = 0;
            // $result[] = array_filter($comment, function ($comment) use ($id, $postId, $name, $email, $body, $count) {
                if (stripos($comment['id'], $id) !== false)
                    $count++;
                if (stripos($comment['postId'], $postId) !== false)
                    $count++;
                if (stripos($comment['name'], $name) !== false)
                    $count++;
                if (stripos($comment['email'], $email) !== false)
                    $count++;
                if (stripos($comment['body'], $body) !== false)
                    $count++;
                if ($count === $totalParameters)
                    $result[] = $comment;

                // return false;
            // });
        }

        // $result = array_search(["id" => $id, "postId" => $postId, "name" => $name, "email" => $email, "body" => $body], $apiResponse);

        dd($result);
    }

    private function getCommentsByCount()
    {
        $apiResponse = $this->sendApiRequest('comments');

        /*$testArray = [
            ['postId' => 3],
            ['postId' => 1],
            ['postId' => 1],
            ['postId' => 1],
            ['postId' => 1],
            ['postId' => 2],
            ['postId' => 2],
        ];*/

        $commentCount = array_map(function($responseArray) {
            return $responseArray->postId;
            // return $responseArray['postId'];
        }, $apiResponse);

        $response = (array_count_values($commentCount));

        arsort($response);

        return json_decode(json_encode($response), true);
    }

    private function sendApiRequest($uri, $formSecureRequest = []) {

        $client = new \GuzzleHttp\Client();

        try {

            $response = $client->request('GET', $this->url . $uri, $formSecureRequest);

            return json_decode( $response->getBody() );

        } catch (GuzzleHttp\Exception\BadResponseException $e) {
            $errors = $e->getMessage();

            return json_decode($errors);
        }
    }
}
