<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('log_d_n_s', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('log_dns_file_id')->constrained('log_d_n_s_files')->onDelete('cascade');
            $table->string('dns')->nullable();
            $table->string('ip_address')->nullable();
            $table->string('classification')->nullable();
            $table->string('timestamp')->nullable();
            $table->longText('ai_response')->nullable();

            $table->date('create_date')->nullable(); // create_date
            $table->date('update_date')->nullable(); // update_date
            $table->date('expiry_date')->nullable(); // expiry_date
            $table->string('country_name')->nullable(); // registrant_contact.country_name
            $table->string('state')->nullable(); // registrant_contact.state
            $table->string('city')->nullable(); // registrant_contact.city
            $table->string('company')->nullable(); // registrant_contact.company
            $table->boolean('status')->default(false);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('log_d_n_s');
    }
};
