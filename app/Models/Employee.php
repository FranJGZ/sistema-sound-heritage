<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class Employee extends Model {
    protected $fillable = ['document_number', 'last_name', 'first_name', 'address', 'phone', 'city_id', 'employee_category_id'];
    public function city() { return $this->belongsTo(City::class); }
    public function employeeCategory() { return $this->belongsTo(EmployeeCategory::class); }
    public function invoices() { return $this->hasMany(Invoice::class); }
}