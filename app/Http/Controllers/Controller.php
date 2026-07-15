<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

/**
 * @OA\Info(
 *      version="1.0.0",
 *      title="Coffee Shop POS API Documentation",
 *      description="Swagger OpenApi description for the Coffee Shop POS system",
 *      @OA\Contact(
 *          email="admin@example.com"
 *      )
 * )
 *
 * @OA\Server(
 *      url=L5_SWAGGER_CONST_HOST,
 *      description="Dynamic API Server"
 * )
 *
 * @OA\Tag(name="Authentication", description="API Endpoints for Authentication")
 * @OA\Tag(name="Orders & POS", description="API Endpoints for POS Orders")
 * @OA\Tag(name="POS Core Data", description="API Endpoints for POS Core Data")
 * @OA\Tag(name="Catalog & Menu", description="API Endpoints for Catalog")
 * @OA\Tag(name="Admin: Users", description="Admin API for Users")
 * @OA\Tag(name="Admin: Branches", description="Admin API for Branches")
 * @OA\Tag(name="Admin: Tables", description="Admin API for Tables")
 * @OA\Tag(name="Admin: Customers", description="Admin API for Customers")
 * @OA\Tag(name="Admin: Categories", description="Admin API for Categories")
 * @OA\Tag(name="Admin: Products", description="Admin API for Products")
 * @OA\Tag(name="Admin: Modifiers", description="Admin API for Modifiers")
 * @OA\Tag(name="Admin: Roles & Permissions", description="Admin API for Roles")
 * @OA\Tag(name="Health", description="API Health Check")
 *
 * @OA\SecurityScheme(
 *     securityScheme="bearerAuth",
 *     type="http",
 *     scheme="bearer",
 *     bearerFormat="JWT"
 * )
 *
 * @OA\Get(
 *     path="/api/health",
 *     tags={"Health"},
 *     summary="Health Check",
 *     description="Check if the API is running",
 *     @OA\Response(
 *         response=200,
 *         description="successful operation"
 *     )
 * )
 */
class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;
}

