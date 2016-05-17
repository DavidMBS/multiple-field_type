<?php namespace Anomaly\MultipleFieldType\Listener;

use Anomaly\MultipleFieldType\MultipleFieldType;
use Anomaly\Streams\Platform\Assignment\Event\AssignmentWasCreated;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Schema\Builder;

/**
 * Class CreatePivotTable
 *
 * @link          http://pyrocms.com/
 * @author        PyroCMS, Inc. <support@pyrocms.com>
 * @author        Ryan Thompson <ryan@pyrocms.com>
 * @package       Anomaly\MultipleFieldType\Listener
 */
class CreatePivotTable
{

    /**
     * The schema builder.
     *
     * @var Builder
     */
    protected $schema;

    /**
     * Create a new StreamSchema instance.
     */
    public function __construct()
    {
        $this->schema = app('db')->connection()->getSchemaBuilder();
    }

    /**
     * Handle the event.
     *
     * @param AssignmentWasCreated $event
     */
    public function handle(AssignmentWasCreated $event)
    {
        $assignment = $event->getAssignment();

        $fieldType = $assignment->getFieldType();

        if (!$fieldType instanceof MultipleFieldType) {
            return;
        }

        $isSortable = $fieldType->config('sortable', false);

        $table = $assignment->getStreamPrefix() . $assignment->getStreamSlug() . '_' . $fieldType->getField();

        $this->schema->dropIfExists($table);

        $this->schema->create(
            $table,
            function (Blueprint $table) use ($isSortable) {

                if ($isSortable) {
                    $table->increments('id');
                }

                $table->integer('entry_id');
                $table->integer('related_id');
                $table->integer('sort_order')->nullable();


                if ($isSortable) {
                    $table->unique(['entry_id', 'related_id']);
                }

                if (!$isSortable) {
                    $table->primary(['entry_id', 'related_id']);
                }
            }
        );
    }
}
