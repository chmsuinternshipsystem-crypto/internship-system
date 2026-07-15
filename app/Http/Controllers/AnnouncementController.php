<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreAnnouncementRequest;
use App\Http\Requests\UpdateAnnouncementRequest;
use App\Models\Announcement;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AnnouncementController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $this->authorize('viewAny', Announcement::class);

        $user = Auth::user();

        $query = Announcement::query()
            ->with('author')
            ->visibleToStaffUser($user)
            ->orderByDesc('created_at');

        $search = trim((string) $request->query('search', ''));
        if ($search !== '') {
            $like = '%'.$search.'%';
            $needle = '%'.strtolower($search).'%';
            $query->where(function ($q) use ($search, $like, $needle) {
                $q->where('title', 'like', $like)
                    ->orWhere('body', 'like', $like)
                    ->orWhereRaw('LOWER(COALESCE(visible_to_role, ?)) LIKE ?', ['', $needle])
                    ->orWhereHas('author', function ($q2) use ($like) {
                        $q2->where('name', 'like', $like);
                    })
                    ->orWhereRaw('CAST(created_at AS CHAR) LIKE ?', [$like])
                    ->orWhereRaw("DATE_FORMAT(created_at, '%b %d, %Y') LIKE ?", [$like]);

                $s = strtolower(trim($search));
                if ($s === 'all' || $s === 'general' || $s === 'everyone' || (bool) preg_match('/all\s*audiences?/i', $search)) {
                    $q->orWhere(function ($sub) {
                        $sub->whereNull('visible_to_role')
                            ->orWhere('visible_to_role', '')
                            ->orWhereRaw('LOWER(visible_to_role) = ?', ['all']);
                    });
                }
            });
        }

        $audience = trim((string) $request->query('audience', ''));
        if ($audience !== '') {
            if ($audience === '__general__') {
                $query->where(function ($q) {
                    $q->whereNull('visible_to_role')
                        ->orWhere('visible_to_role', '')
                        ->orWhereRaw('LOWER(visible_to_role) = ?', ['all']);
                });
            } else {
                $query->whereRaw('LOWER(visible_to_role) = ?', [strtolower($audience)]);
            }
        }

        $authorId = $request->query('author');
        if ($authorId !== null && $authorId !== '') {
            $query->where('created_by', (int) $authorId);
        }

        $announcements = $query->paginate(10)->withQueryString();

        $facetBase = Announcement::query()->visibleToStaffUser($user);

        $rawAudiences = (clone $facetBase)
            ->select('visible_to_role')
            ->distinct()
            ->orderBy('visible_to_role')
            ->pluck('visible_to_role');

        $audienceOptions = [];
        $hasGeneralAudience = false;
        foreach ($rawAudiences as $value) {
            $normalized = strtolower(trim((string) ($value ?? '')));
            if ($normalized === '' || $normalized === 'all') {
                $hasGeneralAudience = true;

                continue;
            }
            $audienceOptions[$normalized] = match ($normalized) {
                'student' => __('Student'),
                'instructor' => __('Instructor'),
                'chairperson' => __('Chairperson'),
                'dean' => __('Dean'),
                default => ucfirst($normalized),
            };
        }
        ksort($audienceOptions);
        if ($hasGeneralAudience) {
            $audienceOptions = ['__general__' => __('All audiences')] + $audienceOptions;
        }

        $authorIdList = (clone $facetBase)
            ->whereNotNull('created_by')
            ->distinct()
            ->orderBy('created_by')
            ->pluck('created_by');

        $filterAuthors = User::query()
            ->whereIn('id', $authorIdList)
            ->orderBy('name')
            ->get(['id', 'name']);

        $hasActiveFilters = $search !== ''
            || $audience !== ''
            || ($authorId !== null && $authorId !== '');

        $canManage = $user->can('manage', Announcement::class);

        $viewData = compact(
            'announcements',
            'search',
            'audience',
            'authorId',
            'audienceOptions',
            'filterAuthors',
            'hasActiveFilters',
            'canManage',
        );

        if (filter_var($request->header('HX-Request'), FILTER_VALIDATE_BOOLEAN)) {
            return view('announcements.partials.ajax-list', [
                'announcements' => $announcements,
                'canManage' => $canManage,
                'audienceOptions' => $audienceOptions,
            ]);
        }

        return view('announcements.index', $viewData);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $this->authorize('manage', Announcement::class);

        return view('announcements.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreAnnouncementRequest $request)
    {
        $this->authorize('manage', Announcement::class);

        $data = $request->validated();
        $data['created_by'] = Auth::id();

        Announcement::create($data);

        return redirect()
            ->route('announcements.index')
            ->with('status', __('Announcement created successfully.'));
    }

    /**
     * Display the specified resource.
     */
    public function show(Announcement $announcement)
    {
        $this->authorize('view', $announcement);

        $announcement->load('author');

        return view('announcements.show', compact('announcement'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Announcement $announcement)
    {
        $this->authorize('manage', Announcement::class);

        return view('announcements.edit', compact('announcement'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateAnnouncementRequest $request, Announcement $announcement)
    {
        $this->authorize('manage', Announcement::class);

        $data = $request->validated();
        $announcement->update($data);

        return redirect()
            ->route('announcements.index')
            ->with('status', __('Announcement updated successfully.'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Announcement $announcement)
    {
        $this->authorize('manage', Announcement::class);

        $announcement->delete();

        return redirect()
            ->route('announcements.index')
            ->with('status', __('Announcement deleted successfully.'))
            ->with('status_type', 'success');
    }
}
