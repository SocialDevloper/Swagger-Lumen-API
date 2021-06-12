<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ProductController extends Controller
{
    /**
     * @OA\Get(
     *      path="/api/products/",
     *      operationId="getProductList",
     *      tags={"Products"},
     *      @OA\Parameter(
     *      name="page_no",
     *      in="query",
     *      required=false,
     *      @OA\Schema(
     *           type="string"
     *      )
     *   ),
     * security={
     *  {"passport": {}},
     *   },
     *      summary="Get list of products",
     *      description="Returns list of products",
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *          @OA\MediaType(
     *           mediaType="application/json",
     *      )
     *      ),
     *      @OA\Response(
     *          response=401,
     *          description="Unauthenticated",
     *      ),
     *      @OA\Response(
     *          response=403,
     *          description="Forbidden"
     *      ),
     * @OA\Response(
     *      response=400,
     *      description="Bad Request"
     *   ),
     * @OA\Response(
     *      response=404,
     *      description="not found"
     *   ),
     *  )
     */

    public function index(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'page_no' => 'nullable|integer',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'      => 'error',
                'status_code' => 422,
                'message'     => $validator->errors(),
            ]);
        }

        $pageNuber = $request->page_no;

        $no_of_records_per_page = env('NO_OF_RECORDS_PER_PAGE');
        $total_rows             = Product::all()->count();

        $total_pages = ceil($total_rows / $no_of_records_per_page);

        // check page number exist for pagination
        if (empty($pageNuber)) {
            $products = Product::all();
        } elseif ($pageNuber <= $total_pages) {
            $products = Product::limit($no_of_records_per_page)->offset(($pageNuber - 1) * $no_of_records_per_page)
            ->get()->toArray();
        } else {
            return response()->json([
                'status' => 'error',
                'status_code' => 422,
                'message' => "Not found page number $request->page_no of data."
            ]);
        }

        return response()->json([
            'status' => 'success',
            'status_code' => 200,
            'message' => [
                'products' => $products
            ]
        ]);
    }

    public function filterPaginatedProduct(Request $request)
    {
        $this->validate($request, [
            'page_no' => 'required|integer',
        ]);

        $pageNuber = $request->page_no;

        $no_of_records_per_page = env('NO_OF_RECORDS_PER_PAGE');
        $total_rows             = Product::all()->count();

        $total_pages = ceil($total_rows / $no_of_records_per_page);

        // check page number exist for pagination
        if ($pageNuber <= $total_pages) {
            return Product::limit($no_of_records_per_page)->offset(($pageNuber - 1) * $no_of_records_per_page)
            ->get()->toArray();
        } else {
            return response()->json(['message' => "Not found page number $request->page_no of data."]);
        }
    }
}
