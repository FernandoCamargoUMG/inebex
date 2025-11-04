<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use Illuminate\Http\Request;
use Carbon\Carbon;

class AppointmentController extends Controller
{
    public function index()
    {
        return Appointment::all();
    }

    public function show($id)
    {
        return Appointment::findOrFail($id);
    }

    public function today()
    {
        return Appointment::whereDate('begin', Carbon::today())
                          ->where('status', 'pending')
                          ->get();
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'appointment_type_id' => 'required|exists:appointment_types,id',
            'title' => 'nullable|string|max:255',
            'begin' => 'required|date',
            'end' => 'required|date|after:begin',
            'status' => 'in:pending,completed,canceled,confirmed',
            'observations' => 'nullable|string'
        ]);

        $validated['status'] = $validated['status'] ?? 'pending';

        return Appointment::create($validated);
    }

    public function update(Request $request, $id)
    {
        $appointment = Appointment::findOrFail($id);
        
        $validated = $request->validate([
            'user_id' => 'exists:users,id',
            'appointment_type_id' => 'exists:appointment_types,id',
            'title' => 'nullable|string|max:255',
            'begin' => 'date',
            'end' => 'date|after:begin',
            'status' => 'in:pending,completed,canceled,confirmed',
            'observations' => 'nullable|string'
        ]);

        $appointment->update($validated);
        return $appointment;
    }

    public function destroy($id)
    {
        $appointment = Appointment::findOrFail($id);
        $appointment->delete();
        return response()->json(['message' => 'Appointment deleted successfully']);
    }
}