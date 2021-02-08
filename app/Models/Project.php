<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Project extends Model {
    use HasFactory;

    public static function obj_to_array($data): array {
        return [
            'id' => $data->id,
            'name' => $data->name,
            'client' => $data->client,
            'active' => $data->active,
        ];
    }

    public static function read_one($id): array {
        $data = self::where('id', $id)->first();
        return self::obj_to_array($data);
    }

    public static function update_project($data) {
        $project = self::find($data['id']);
        return self::write($data, $project);
    }

    public static function read_all($binary_active=False): array {
        $data = [];
        foreach (self::all()->getIterator() as $item) {
            $add = self::obj_to_array($item);
            array_push($data, $add);
        }
        return $data;
    }

    public static function create(array $data) {
        return self::write($data);
    }

    public static function write(array $data, $project=False) {
        if ($project == False) {
            $project = new self();
        }
        $project->name = $data['name'];
        $project->client = $data['client'];
        $project->active = $data['active'];
        $project->save();
        return self::obj_to_array($project);
    }

    public static function delete_project($id) {
        $item = self::find($id);
        $item->delete();
    }
}
