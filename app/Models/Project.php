<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Project extends Model {
    use HasFactory;

    // Convert object to array
    public static function obj_to_array($data): array {
        return (array) $data->attributes;
    }

    // Return a single project
    public static function read_one($id): array {
        $data = self::find($id);
        return self::obj_to_array($data);
    }

    // Update project and return data
    public static function update_project($data) {
        $project = self::find($data['id']);
        return self::write($data, $project);
    }

    // Read all projects
    public static function read_all(): array {
        $data = [];
        foreach (self::all()->getIterator() as $item) {
            $add = self::obj_to_array($item);
            array_push($data, $add);
        }
        return $data;
    }

    // Create new project and return data
    public static function create(array $data) {
        return self::write($data);
    }

    // Write to db and return data
    public static function write(array $data, $project=False) {
        if ($project == False) {
            $project = new self();
        }
        $project->name = $data['name'];
        $project->client = $data['client'];
        $project->active = $data['active'];
        $project->save();
        return self::read_one($data['id']);
    }

    // Delete one from DB
    public static function delete_project($id) {
        $item = self::find($id);
        $item->delete();
    }
}
