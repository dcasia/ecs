<?php

declare(strict_types = 1);

final class VisitorEventsMigration
{
    public function down(): void
    {
        Schema::dropIfExists('visitor_events');


    }


}

final class CleanVisitorEventsMigration
{
    public function down(): void
    {
        Schema::dropIfExists('visitor_events');
    }
}
