<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private function hasIndex(string $table, string $index): bool
    {
        return Schema::getConnection()->getSchemaBuilder()->hasIndex($table, $index);
    }

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::hasTable('users')) {
            Schema::table('users', function (Blueprint $table): void {
                if (! Schema::hasColumn('users', 'role')) {
                    $table->string('role', 20)->default('client')->after('password');
                }

                if (! Schema::hasColumn('users', 'dietary_tags')) {
                    $table->json('dietary_tags')->nullable()->after('role');
                }

                if (! $this->hasIndex('users', 'users_role_index')) {
                    $table->index('role');
                }
            });
        }

        if (Schema::hasTable('categories')) {
            Schema::table('categories', function (Blueprint $table): void {
                // V2 keeps this legacy column during transition, but makes it optional.
                if (Schema::hasColumn('categories', 'restaurant_id')) {
                    $table->unsignedBigInteger('restaurant_id')->nullable()->change();
                }

                if (Schema::hasColumn('categories', 'name')) {
                    $table->string('name', 100)->change();
                }

                if (Schema::hasColumn('categories', 'description')) {
                    $table->text('description')->nullable()->change();
                }

                if (! Schema::hasColumn('categories', 'color')) {
                    $table->string('color')->nullable()->after('description');
                }

                if (! Schema::hasColumn('categories', 'is_active')) {
                    $table->boolean('is_active')->default(true)->after('color');
                }
            });

            Schema::table('categories', function (Blueprint $table): void {
                if (! $this->hasIndex('categories', 'categories_name_unique')) {
                    $table->unique('name');
                }
            });
        }

        if (Schema::hasTable('plates')) {
            Schema::table('plates', function (Blueprint $table): void {
                if (! Schema::hasColumn('plates', 'category_id')) {
                    $table->foreignId('category_id')->nullable()->after('id')->constrained()->nullOnDelete();
                }

                if (Schema::hasColumn('plates', 'description')) {
                    $table->text('description')->nullable()->change();
                }

                if (Schema::hasColumn('plates', 'is_available')) {
                    $table->boolean('is_available')->default(true)->change();
                }

                if (! Schema::hasColumn('plates', 'image')) {
                    $table->string('image')->nullable()->after('price');
                }
            });
        }

        if (Schema::hasTable('ingredients')) {
            Schema::table('ingredients', function (Blueprint $table): void {
                if (! Schema::hasColumn('ingredients', 'name')) {
                    $table->string('name');
                }

                if (! Schema::hasColumn('ingredients', 'tags')) {
                    $table->json('tags')->default(json_encode([]));
                }
            });
        }

        if (Schema::hasTable('recommendations')) {
            Schema::table('recommendations', function (Blueprint $table): void {
                if (! Schema::hasColumn('recommendations', 'user_id')) {
                    $table->foreignId('user_id')->nullable()->after('id')->constrained()->cascadeOnDelete();
                }

                if (! Schema::hasColumn('recommendations', 'plate_id')) {
                    $table->foreignId('plate_id')->nullable()->after('user_id')->constrained()->cascadeOnDelete();
                }

                if (! Schema::hasColumn('recommendations', 'score')) {
                    $table->decimal('score', 5, 2)->nullable()->after('plate_id');
                }

                if (! Schema::hasColumn('recommendations', 'label')) {
                    $table->string('label')->nullable()->after('score');
                }

                if (! Schema::hasColumn('recommendations', 'warning_message')) {
                    $table->text('warning_message')->nullable()->after('label');
                }

                if (! Schema::hasColumn('recommendations', 'conflicting_tags')) {
                    $table->json('conflicting_tags')->nullable()->after('warning_message');
                }

                if (! Schema::hasColumn('recommendations', 'status')) {
                    $table->string('status')->default('pending')->after('conflicting_tags');
                }

                if (! $this->hasIndex('recommendations', 'recommendations_status_index')) {
                    $table->index('status');
                }
            });
        }

        if (! Schema::hasTable('plate_ingredient')) {
            Schema::create('plate_ingredient', function (Blueprint $table): void {
                $table->foreignId('plate_id')->constrained()->cascadeOnDelete();
                $table->foreignId('ingredient_id')->constrained()->cascadeOnDelete();
                $table->primary(['plate_id', 'ingredient_id']);
                $table->index('ingredient_id');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('plate_ingredient');

        if (Schema::hasTable('recommendations')) {
            Schema::table('recommendations', function (Blueprint $table): void {
                if ($this->hasIndex('recommendations', 'recommendations_status_index')) {
                    $table->dropIndex('recommendations_status_index');
                }

                if (Schema::hasColumn('recommendations', 'status')) {
                    $table->dropColumn('status');
                }

                if (Schema::hasColumn('recommendations', 'conflicting_tags')) {
                    $table->dropColumn('conflicting_tags');
                }

                if (Schema::hasColumn('recommendations', 'warning_message')) {
                    $table->dropColumn('warning_message');
                }

                if (Schema::hasColumn('recommendations', 'label')) {
                    $table->dropColumn('label');
                }

                if (Schema::hasColumn('recommendations', 'score')) {
                    $table->dropColumn('score');
                }

                if (Schema::hasColumn('recommendations', 'plate_id')) {
                    $table->dropConstrainedForeignId('plate_id');
                }

                if (Schema::hasColumn('recommendations', 'user_id')) {
                    $table->dropConstrainedForeignId('user_id');
                }
            });
        }

        if (Schema::hasTable('ingredients')) {
            Schema::table('ingredients', function (Blueprint $table): void {
                if (Schema::hasColumn('ingredients', 'tags')) {
                    $table->dropColumn('tags');
                }

                if (Schema::hasColumn('ingredients', 'name')) {
                    $table->dropColumn('name');
                }
            });
        }

        if (Schema::hasTable('plates')) {
            Schema::table('plates', function (Blueprint $table): void {
                if (Schema::hasColumn('plates', 'image')) {
                    $table->dropColumn('image');
                }

                if (Schema::hasColumn('plates', 'category_id')) {
                    $table->dropConstrainedForeignId('category_id');
                }
            });
        }

        if (Schema::hasTable('categories')) {
            Schema::table('categories', function (Blueprint $table): void {
                if ($this->hasIndex('categories', 'categories_name_unique')) {
                    $table->dropUnique('categories_name_unique');
                }

                if (Schema::hasColumn('categories', 'is_active')) {
                    $table->dropColumn('is_active');
                }

                if (Schema::hasColumn('categories', 'color')) {
                    $table->dropColumn('color');
                }
            });
        }

        if (Schema::hasTable('users')) {
            Schema::table('users', function (Blueprint $table): void {
                if ($this->hasIndex('users', 'users_role_index')) {
                    $table->dropIndex('users_role_index');
                }

                if (Schema::hasColumn('users', 'dietary_tags')) {
                    $table->dropColumn('dietary_tags');
                }

                if (Schema::hasColumn('users', 'role')) {
                    $table->dropColumn('role');
                }
            });
        }
    }
};
