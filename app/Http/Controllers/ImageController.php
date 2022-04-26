<?php

namespace App\Http\Controllers;

use App\Models\Image;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ImageController extends Controller
{
    private $model;

    public function __construct(Image $image)
    {
        $this->model = $image;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $images = $this->model->all();

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
        if ($request->image == null) {
            return response()->json([
                'message' => 'Your request have a empty body'
            ], 400);
        }

        $requestData = $request->all();

        if ($request->image->isValid()) {
            $requestData = $this->imageNameFormatter($request->image);
        };

        $createdImage = $this->model->create($requestData);

        return response()->json($createdImage, 201);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        if (!$image = $this->model->find($id)) {
            return response()->json(['message' => 'No such image with this id'], 400);
        };

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
        if (!$image = $this->model->find($id)) {
            return response()->json(['message' => 'No such image with this id'], 400);
        };

        if ($request->image == null) {
            return response()->json(['message' => 'Your request have a empty body'], 400);
        }

        $requestData = $request->all();

        if ($request->image && $request->image->isValid()) {
            $this->findAndDestroyFile($image->path);

            $requestData = $this->imageNameFormatter($request->image);
        };

        $image->update($requestData);

        $updatedImage = $image->refresh();

        return response()->json($updatedImage);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        if (!$image = $this->model->find($id)) {
            return response()->json([
                'message' => 'No such image with this ID'
            ], 400);
        };

        $this->findAndDestroyFile($image->path);

        $image->delete();

        return response()->json([
            'message' => 'Image successful deleted'
        ]);
    }

    private function findAndDestroyFile($imagePath)
    {
        if (Storage::exists($imagePath)) {
            Storage::delete($imagePath);
        }
    }

    private function imageNameFormatter($image)
    {
        $nameFile = $image->hashName();

        $file = $image->storeAs('images', $nameFile);

        $formattedData['image'] = $file;
        $formattedData['name'] = $nameFile;
        $formattedData['path'] = $file;

        return $formattedData;
    }
}
