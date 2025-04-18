<?php

namespace AnourValar\EloquentSerialize\Tests;

use AnourValar\EloquentSerialize\Tests\Models\Book;
use AnourValar\EloquentSerialize\Tests\Models\User;
use AnourValar\EloquentSerialize\Tests\Models\UserPhone;
use AnourValar\EloquentSerialize\Tests\Models\Post;
use AnourValar\EloquentSerialize\Tests\Models\UserPhoneNote;
use AnourValar\EloquentSerialize\Tests\Models\Tag;
use Illuminate\Database\Eloquent\Relations\HasOneOrMany;

class EagerTest extends AbstractSuite
{
    /**
     * @return void
     */
    public function testSimple()
    {
        // ...
        $this->compare(User::query());

        // with
        $this->compare(User::with('userPhones'));

        // with count
        $this->compare(User::withCount('userPhones'));
    }

    /**
     * @return void
     */
    public function testComplex()
    {
        // with
        $this->compare(User::with('userPhonesSorted'));
        $this->compare(User::with('userPhonesPrimary'));
        $this->compare(User::with(['userPhonesSorted', 'userPhonesPrimary']));
        $this->compare(User::query()->with('filesAB', 'filesC', 'filesDE'));
        $this->compare(User::with('books'));

        // with count
        $this->compare(User::withCount('userPhonesSorted'));
        $this->compare(User::withCount('userPhonesPrimary'));
        $this->compare(User::withCount(['userPhonesSorted', 'userPhonesPrimary']));
        $this->compare(User::query()->withCount('filesAB', 'filesC', 'filesDE'));
        $this->compare(User::withCount('books'));
    }

    /**
     * @return void
     */
    public function testNested()
    {
        // with
        $this->compare(User::with('userPhones.userPhoneNote'));
        $this->compare(User::with('userPhones.userPhoneNote:id,user_phone_id,note'));
        $this->compare(User::with(['userPhones' => ['userPhoneNote']]));
        $this->compare(User::with(['userPhones' => fn ($query) => $query->with('userPhoneNote')]));
        $this->compare(User::with(['books' => fn ($query) => $query->select(['title'])]));

        // with (reverse)
        $this->compare(UserPhone::with('user.userPhones'));
        $this->compare(UserPhone::with(['user' => ['userPhones']]));
    }

    /**
     * @return void
     */
    public function testNestedComplex()
    {
        // with
        $this->compare(User::with('userPhonesSorted.userPhoneNote'));
        $this->compare(User::with('userPhonesPrimary.userPhoneNote'));

        // with (reverse)
        $this->compare(UserPhone::with('user.userPhonesSorted'));
        $this->compare(UserPhone::with('user.userPhonesPrimary'));
    }

    /**
     * @return void
     */
    public function testWithBuilder()
    {
        // 1 level
        $this->compare(
            User::with(['userPhones' => function ($query) {
                $query->orderBy('id', 'ASC')->limit(1)->select(['id', 'phone']);
            }])
        );

        // 2 levels
        $this->compare(
            User::with(['userPhones' => function ($query) {
                $query->where(function ($query) {
                    $query->where('phone', '=', '111')->orWhere('phone', '!=', '222')->limit(5);
                });
            }])
        );

        // 3 levels
        $this->compare(
            User::with(['userPhones' => function ($query) {
                $query->where(function ($query) {
                    $query
                        ->where('phone', '=', '111')
                        ->orWhere(function ($query) {
                            $query->where('phone', '=', '222')->orWhere('created_at', '>', '2010-01-01');
                        });
                });
            }])
        );
    }

    /**
     * @return void
     */
    public function testWithComplexBuilder()
    {
        // 1 level
        $this->compare(
            User::with(['userPhonesPrimary' => function ($query) {
                $query->orderBy('id', 'ASC')->limit(1)->select(['id', 'phone']);
            }])
        );

        // 2 levels
        $this->compare(
            User::with(['userPhonesPrimary' => function ($query) {
                $query->where(function ($query) {
                    $query->where('phone', '=', '111')->orWhere('phone', '!=', '222');
                });
            }])
        );

        // 3 levels
        $this->compare(
            User::with(['userPhonesPrimary' => function ($query) {
                $query->where(function ($query) {
                    $query
                        ->where('phone', '=', '111')
                        ->orWhere(function ($query) {
                            $query->where('phone', '=', '222')->orWhere('created_at', '>', '2010-01-01');
                        });
                });
            }])
        );
    }

    /**
     * @return void
     */
    public function testWithCountBuilder()
    {
        // 1 level
        $this->compare(
            User::withCount(['userPhones' => function ($query) {
                $query->limit(2);
            }])
        );

        // 2 levels
        $this->compare(
            User::withCount(['userPhones' => function ($query) {
                $query->where(function ($query) {
                    $query->where('phone', '=', '111')->orWhere('phone', '!=', '222');
                });
            }])
        );
    }

    /**
     * @return void
     */
    public function testWithComplexCountBuilder()
    {
        // 1 level
        $this->compare(
            User::withCount(['userPhonesSorted' => function ($query) {
                $query->limit(2);
            }])
        );

        // 2 levels
        $this->compare(
            User::withCount(['userPhonesSorted' => function ($query) {
                $query->where(function ($query) {
                    $query->where('phone', '=', '111')->orWhere('phone', '!=', '222');
                });
            }])
        );
    }

    /**
     * @return void
     */
    public function testWithCountAlias()
    {
        // simple
        $this->compare(
            User::withCount('userPhones as test')
        );

        // builder
        $this->compare(
            User::withCount([
                'userPhones as primary' => function ($query) {
                    $query->where(function ($query) {
                        $query->where('is_primary', true);
                    });
                },
                'userPhones as not_primary' => function ($query) {
                    $query->where('is_primary', false);
                },
            ])
        );
    }

    /**
     * @return void
     */
    public function testBelongs()
    {
        // simple
        $this->compare(
            UserPhone::with('user')
        );

        // simple count
        $this->compare(
            UserPhone::withCount('user')
        );

        // builder
        $this->compare(
            UserPhone::with([
                'user' => function ($query) {
                    $query->where('title', '=', 'admin')->limit(1);
                },
            ])
        );
    }

    /**
     * @return void
     */
    public function testHasManyThrough()
    {
        // simple
        $this->compare(User::with('userPhoneNote'));
        $this->compare(User::with('userPhonesSorted'));
        $this->compare(User::with('userPhonesPrimary'));

        // simple count
        $this->compare(User::withCount('userPhoneNote'));
        $this->compare(User::withCount('userPhonesSorted'));
        $this->compare(User::withCount('userPhonesPrimary'));

        // builder
        $this->compare(
            User::with([
                'userPhoneNote' => function ($query) {
                    $query->limit(1);
                },
            ])
        );
        $this->compare(
            User::with([
                'userPhonesSorted' => function ($query) {
                    $query->limit(1);
                },
            ])
        );
        $this->compare(
            User::with([
                'userPhonesPrimary' => function ($query) {
                    $query->limit(1);
                },
            ])
        );
    }

    /**
     * @return void
     */
    public function testThroughBuilder()
    {
        // simple
        $this->compare(User::with('userPhoneNoteAlt'));

        // simple count
        $this->compare(User::withCount('userPhoneNoteAlt'));

        // builder
        $this->compare(
            User::with([
                'userPhoneNoteAlt' => function ($query) {
                    $query->limit(1);
                },
            ])
        );
    }

    /**
     * @return void
     */
    public function testHasOne()
    {
        // simple
        $this->compare(
            UserPhone::with('userPhoneNote')
        );

        // simple count
        $this->compare(
            UserPhone::withCount('userPhoneNote')
        );

        // builder
        $this->compare(
            UserPhone::with([
                'userPhoneNote' => function ($query) {
                    $query->whereNotNull('note');
                },
            ])
        );
    }

    /**
     * @return void
     */
    public function testMorphTo()
    {
        // Nested
        $this->compare(
            Tag::with(['taggable' => function (\Illuminate\Database\Eloquent\Relations\MorphTo $morphTo) {
                $morphTo->morphWith([
                    Post::class => ['user'],
                    UserPhone::class => ['userPhoneNote'],
                ]);
            }])
        );

        // Nested count
        $this->compare(
            Tag::with(['taggable' => function (\Illuminate\Database\Eloquent\Relations\MorphTo $morphTo) {
                $morphTo->morphWithCount([
                    Post::class => ['user'],
                    UserPhone::class => ['userPhoneNote'],
                ]);
            }])
        );

        // Nested (reverse)
        $this->compare(
            Post::with(['tag' => function (\Illuminate\Database\Eloquent\Relations\MorphOne $morphOne) {
                $morphOne->with('taggable');
            }])
        );

        // Nested - closure
        $this->compare(
            Tag::with(['taggable' => function (\Illuminate\Database\Eloquent\Relations\MorphTo $morphTo) {
                $morphTo->morphWith([
                    Post::class => [
                        'user' => fn ($query) => $query->with('userPhones')->where('id', '>', 0)->whereIn('sort', [1, 2, 3, 4, 5]),
                        'tag' => fn ($query) => $query->where('id', '>', 5),
                    ],
                    UserPhone::class => ['user', 'userPhoneNote' => fn ($query) => $query->whereNotNull('note')],
                ]);
            }])
        );

        // Nested count - closure
        $this->compare(
            Tag::with(['taggable' => function (\Illuminate\Database\Eloquent\Relations\MorphTo $morphTo) {
                $morphTo->morphWithCount([
                    Post::class => [
                        'user' => fn ($query) => $query->with('userPhones')->where('id', '>', 0)->whereIn('sort', [1, 2, 3, 4, 5]),
                        'tag' => fn ($query) => $query->where('id', '>', 5),
                    ],
                    UserPhone::class => ['user', 'userPhoneNote' => fn ($query) => $query->whereNotNull('note')],
                ]);
            }])
        );
    }

    /**
     * @return void
     */
    public function testChaperone()
    {
        if (! in_array(\Illuminate\Database\Eloquent\Relations\Concerns\SupportsInverseRelations::class, class_uses(HasOneOrMany::class))) {
            $this->markTestSkipped('Laravel 11.22+ feature');
        }

        $this->compare(User::with(['userPhones' => fn ($query) => $query->chaperone()]));

        $this->compare(User::with('userPhonesChaperone'));
        $this->compare(User::with(['userPhonesChaperone' => fn ($query) => $query->withoutChaperone()]));
    }

    /**
     * @return void
     */
    public function testBelongsToMany()
    {
        $this->compare(User::with(['books' => fn ($query) => $query->withPivot('book_id')]));

        $this->compare(User::has('books'));
        $this->compare(Book::has('users'));

        $this->compare(User::whereHas('books', fn ($query) => $query->where('book_user.id', '>', 0)));
    }
}
