<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\ProductBranches;

class ProductBranchesController extends Controller
{
    /**
     * Force delete all soft-deleted ProductBranches records
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    public function forceDeleteSoftDeleted()
    {
        try {
            // Get count of soft-deleted records before deletion
            $softDeletedCount = ProductBranches::onlyTrashed()->count();
            
            if ($softDeletedCount === 0) {
                return response()->json([
                    'code' => 200,
                    'message' => 'No soft-deleted product branches found to force delete',
                    'data' => [
                        'deleted_count' => 0
                    ]
                ], 200);
            }
            
            // Force delete all soft-deleted records
            ProductBranches::onlyTrashed()->forceDelete();
            
            return response()->json([
                'code' => 200,
                'message' => 'Successfully force deleted all soft-deleted product branches',
                'data' => [
                    'deleted_count' => $softDeletedCount
                ]
            ], 200);
            
        } catch (\Exception $e) {
            return response()->json([
                'code' => 500,
                'message' => 'Failed to force delete soft-deleted product branches',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
