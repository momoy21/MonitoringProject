<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\BidangJasa;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Spatie\Permission\Models\Role;

class RegisterController extends Controller
{
    /**
     * Display the registration form for creating new PM users.
     */
    public function index(Request $request)
    {
        $query = User::with('roles')->whereHas('roles', function ($query) {
            $query->where('name', 'Project Manager');
        });

        // Filter by bidang jasa if provided
        if ($request->filled('bidang_jasa')) {
            $bidangJasaId = $request->bidang_jasa;
            $query->whereRaw("JSON_CONTAINS(bidang_jasa_ids, '\"$bidangJasaId\"')");
        }

        // Order by newest first
        $query->orderBy('created_at', 'desc');

        $users = $query->paginate(10)->appends($request->except('page'));

        // Get all bidang jasa for filter dropdown
        $bidangJasas = BidangJasa::active()->orderBy('desc_bidjasa')->get();

        return view('register.index', compact('users', 'bidangJasas'));
    }

    /**
     * Show the form for creating a new PM user.
     */
    public function create()
    {
        $bidangJasas = BidangJasa::active()->orderBy('desc_bidjasa')->get();
        return view('register.create', compact('bidangJasas'));
    }

    /**
     * Store a newly created PM user.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'bidang_jasa_ids' => ['nullable', 'array'],
            'bidang_jasa_ids.*' => ['exists:bidangjasa,id_bidjasa'],
        ]);

        // Default password: p@ssw0rd4j4
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make('p@ssw0rd4j4'),
            'bidang_jasa_ids' => $request->bidang_jasa_ids ? json_encode($request->bidang_jasa_ids) : null,
        ]);

        // Assign Project Manager role
        $pmRole = Role::firstOrCreate(['name' => 'Project Manager']);
        $user->assignRole($pmRole);

        return redirect()->route('register.index')
            ->with('success', 'Project Manager berhasil didaftarkan dengan password default: password');
    }

    /**
     * Display the specified PM user detail.
     */
    public function show($id)
    {
        $user = User::findOrFail($id);

        // Ensure user is PM
        if (!$user->hasRole('Project Manager')) {
            abort(403, 'User bukan Project Manager');
        }

        $bidangJasaIds = $user->bidang_jasa_ids ? json_decode($user->bidang_jasa_ids, true) : [];
        $bidangJasas = BidangJasa::whereIn('id_bidjasa', $bidangJasaIds)->get();

        return response()->json([
            'success' => true,
            'data' => [
                'user' => $user,
                'bidangJasas' => $bidangJasas
            ]
        ]);
    }

    /**
     * Show the form for editing the specified PM user.
     */
    public function edit($id)
    {
        $user = User::findOrFail($id);

        // Ensure user is PM
        if (!$user->hasRole('Project Manager')) {
            abort(403, 'User bukan Project Manager');
        }

        $bidangJasas = BidangJasa::active()->orderBy('desc_bidjasa')->get();
        $selectedBidangJasas = $user->bidang_jasa_ids ? json_decode($user->bidang_jasa_ids, true) : [];

        return view('register.edit', compact('user', 'bidangJasas', 'selectedBidangJasas'));
    }

    /**
     * Update the specified PM user.
     */
    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);

        // Ensure user is PM
        if (!$user->hasRole('Project Manager')) {
            abort(403, 'User bukan Project Manager');
        }

        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email,' . $user->id],
            'password' => ['nullable', 'confirmed', Rules\Password::defaults()],
            'bidang_jasa_ids' => ['nullable', 'array'],
            'bidang_jasa_ids.*' => ['exists:bidangjasa,id_bidjasa'],
        ]);

        $user->name = $request->name;
        $user->email = $request->email;
        $user->bidang_jasa_ids = $request->bidang_jasa_ids ? json_encode($request->bidang_jasa_ids) : null;

        if ($request->filled('password')) {
            $user->password = Hash::make($request->password);
        }

        $user->save();

        return redirect()->route('register.index')
            ->with('success', 'Project Manager berhasil diupdate!');
    }

    /**
     * Remove the specified PM user.
     */
    public function destroy($id)
    {
        $user = User::findOrFail($id);

        // Ensure user is PM
        if (!$user->hasRole('Project Manager')) {
            abort(403, 'User bukan Project Manager');
        }

        $user->delete();

        return redirect()->route('register.index')
            ->with('success', 'Project Manager berhasil dihapus!');
    }
}
