<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\v1\CountryControllerStoreRequest;
use App\Models\Country;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;

class CountryController extends Controller
{
    public function index(Request $request): \Illuminate\Http\JsonResponse
    {

        $countries = Country::paginate(10);

        return response()->json($countries);
    }

    public function create(Request $request): Response
    {
        $country = Country::find($id);

        return view('country.create', compact('user'));
    }

    public function store(CountryControllerStoreRequest $request): Response
    {
        $country = Country::create($request->validated());

        return redirect()->route('country.show', ['country' => $country]);
    }

    public function show(Request $request, Country $country): Response
    {
        $countries = Country::all();

        return view('country.show', compact('country', 'comments'));
    }
}
