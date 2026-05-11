<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class DeleteFilemanagerController
{
    public function filemanagerDelete(Request $request)
    {
        $reff = explode("/", $_SERVER['HTTP_REFERER']);
        $table = $reff[4];
        if ($table == "public") {
            $table = $reff[6];
        }
        \DB::table($table)->find($request->id);

        $update = [];
        $update[$request->column] = null;
        \DB::table($table)->where("id", $request->id)->update($update);

        return redirect($_SERVER['HTTP_REFERER']);
    }
}
?>