<?php

namespace App\Http\Controllers;

use App\Models\Image;
use Faker\Provider\Uuid;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ImageController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $images = Image::all();

        return response()->json($images);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $data = $request->all();

        if ($request->image->isValid()) {
            $nameFile = Uuid::uuid() . '.' . $request->image->getClientOriginalExtension();

            $file = $request->image->storeAs('images', $nameFile);

            $data['image'] = $file;
            $data['name'] = $nameFile;
            $data['path'] = $file;
        };

        $createdImage = Image::create($data);

        return response()->json([
            'image' => $createdImage,
            'link' => url("storage/{$createdImage->path}")
        ]);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $image = Image::find($id);

        return response()->json($image);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        if (!$image = Image::find($id)) {
            return response()->json(['message' => 'No such image with this id']);
        };

        $data = $request->all();

        if ($request->image && $request->image->isValid()) {
            if (Storage::exists($image->path)) {
                Storage::delete($image->path);
            }

            $nameFile = Uuid::uuid() . '.' . $request->image->getClientOriginalExtension();

            $file = $request->image->storeAs('images', $nameFile);

            $data['image'] = $file;
            $data['name'] = $nameFile;
            $data['path'] = $file;
        };

        $image->update($data);

        $updatedImage = $image->refresh();

        return response()->json([
            'message' => 'Image successful updated!',
            'image' => $updatedImage,
            'link' => url("storage/{$updatedImage->path}")
        ]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        if (!$image = Image::find($id)) {
            return response()->json([
                'message' => 'No such image with this ID'
            ]);
        };

        if (Storage::exists($image->path)) {
            Storage::delete($image->path);
        }

        $image->delete();

        return response()->json([
            'message' => 'Image successful deleted'
        ]);
    }
}
