<?php

namespace App\Controllers;

use App\Models\Group;
use App\Models\Post;
use DateTime;
use function React\Promise\map;

class GroupController extends Controller
{
    public function listed($request, $response)
    {
        $groups = Group::with(['members' => function ($query) {
            $query->orderBy('role', 'desc');
        }])
            ->whereHas('members', function ($q) {
                $q->where('user', $this->container->me);
            })
            ->get()->each(function ($group) {
                $group->members->each(function ($user) use ($group) {
                    $user->admin = $group->admin == $user->id;
                });
                $group->owner = $group->admin == $this->container->me;
            });
        return $groups->toJson();
    }

    public function create($request, $response)
    {
        $input = $request->getParsedBody();
        $now = (new DateTime())->getTimestamp() * 1000;

        $group = Group::create([
            'name' => $input['name'],
            'description' => $input['description'],
            'admin' => $this->container->me,
            'theme' => $input['theme']
        ]);
        $message = [
            'content' => 'GROUP',
            'id' => $this->container->me,
            'name' => $group['name'],
            'date' => $now,
            'type' => 1
        ];
        $group->members()->attach($this->container->me, [
            'role' => 1,
            'status' => 1,
            'date' => $now
        ]);
        array_map(function ($id) use ($group, $now, $message) {
            $group->members()->attach($id, [
                'role' => 0,
                'status' => 1,
                'date' => $now
            ]);
        }, explode(',', $input['members']));
        $this->pushNotification($input['members'],
            NOTIFY_PULL,
            1,
            CHANNEL_GROUP,
            [$this->container->me, $group['id']],
            [MEAN_A_USER, MEAN_A_GROUP]);
        $response->write(json_encode(array('success' => true, 'id' => $group['id'])));
        return $response;
    }

    public function update($request, $response)
    {
        $input = $request->getParsedBody();
        $now = (new DateTime())->getTimestamp() * 1000;

        $group = Group::find($input['id']);
        $group->description = $input['description'];
        $group->theme = $input['theme'];
        $group->save();
        $members = $group->members()->get()->map(function($i){return $i->id;})->toArray();
        $this->pushNotification($members, NOTIFY_PULL, 2, CHANNEL_GROUP, [$this->container->me, $group['id']], [MEAN_A_USER, MEAN_A_GROUP]);
        $response->write(json_encode(array('success' => true)));
        return $response;
    }

    public function listPost($request, $response)
    {
        $groups = Group::find($request->getQueryParams()['group']);
        if (!$groups) {
            $response->write(json_encode($this->message['404']));
            return $response->withStatus(404);
        }
        $access = $groups->members->contains('id', $this->container->me);
        if ($access) {
            $response->write($groups->posts->toJson());
            return $response;
        } else {
            $response->write(json_encode($this->message['403']));
            return $response->withStatus(403);
        }
    }

    public function newPost($request, $response)
    {
        $input = $request->getParsedBody();
        $groups = Group::find($input['group']);
        if (!$groups) {
            $response->write(json_encode($this->message['404']));
            return $response->withStatus(404);
        }
        $access = $groups->members->contains('id', $this->container->me);
        if ($access) {
            $groups->posts()->create([
                'user' => $this->container->me,
                'description' => $input['description'],
                'lat' => $input['lat'],
                'lng' => $input['lng'],
                'expire' => $input['expire']
            ]);

            $response->write(['success' => true]);
            return $response;
        } else {
            $response->write(json_encode($this->message['403']));
            return $response->withStatus(403);
        }
    }

    public function editPost($request, $response)
    {
        $input = $request->getParsedBody();
        $post = Post::find($input['id']);
        if (!$post) {
            $response->write(json_encode($this->message['404']));
            return $response->withStatus(404);
        }
        $access = $post->user == $this->container->me;
        if ($access) {
            $post->description = $input['description'];
            $post->lat = $input['lat'];
            $post->lng = $input['lng'];
            $post->expire = $input['expire'];
            $post->save();
            
            $response->write(json_encode(['success' => true]));
            return $response;
        } else {
            $response->write(json_encode($this->message['403']));
            return $response->withStatus(403);
        }
    }
}