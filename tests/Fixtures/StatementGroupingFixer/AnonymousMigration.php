<?php

declare(strict_types = 1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration
{
    protected $connection = 'events';

    public function up(): void
    {
        Schema::create('visitor_events', function (Blueprint $table) {

            $table->id();
            $table->char('visitor_id', 26);
            $table->string('event_name', 255);
            $table->string('event_action', 255);
            $table->text('origin_url')->nullable();
            $table->jsonb('metadata')->nullable();
            $table->timestamps();

            $table->foreign('visitor_id')->references('visitor_id')->on('visitors');

        });
    }

    public function down(): void
    {
        Schema::dropIfExists('visitor_events');
    }
};
