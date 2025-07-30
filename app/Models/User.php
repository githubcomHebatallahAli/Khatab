<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Tymon\JWTAuth\Contracts\JWTSubject;

class User extends Authenticatable implements JWTSubject
{

    use HasFactory, Notifiable, SoftDeletes;
    const storageFolder= 'Student';


    protected $fillable = [
        'name',
        'email',
        'password',
         'studentPhoNum',
        'parentPhoNum',
        'grade_id',
        'governorate',
        'parnt_id',
        'img',
        'parent_code',
        'email_verified_at',

    ];


    protected $hidden = [
        'password',
        'remember_token',
    ];

   
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

        public function parent()
{
    return $this->belongsTo(Parnt::class, 'parnt_id');
}

public function transactions()
{
    return $this->hasMany(PaymobTransaction::class, 'user_id');
}


    public function reactions()
{
    return $this->morphMany(Reaction::class, 'reactable');
}


    public function exams()
    {
        return $this->belongsToMany(Exam::class,'student_exams')
                    ->withPivot('score', 'has_attempted','started_at',
                    'submitted_at','time_taken','correctAnswers')
                    ->withTimestamps();
    }

    public function courses()
    {
        return $this->belongsToMany(Course::class, 'student_courses')
                    ->withPivot('purchase_date', 'status','byAdmin');
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }


    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    /**
     * Return a key value array, containing any custom claims to be added to the JWT.
     *
     * @return array
     */
    public function getJWTCustomClaims()
    {
        return [];
    }


    public function student()
    {
        return $this->hasMany(Student::class);
    }


    public function grade()
    {
        return $this->belongsTo(Grade::class);
    }


    public function Answers()
    {
        return $this->hasMany(Answer::class, 'user_id');
    }
}
