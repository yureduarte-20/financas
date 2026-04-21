@props([
    'id' => 'id_'.md5(\Illuminate\Support\Str::random()),
    'timeout' => 5000
])
@if($errors->any())
    <div x-init="setTimeout(() => $el.remove(), {{$timeout}})"
         class="bg-danger-50 border border-danger-200 text-sm text-danger-800 rounded-lg p-4 dark:bg-danger/10 dark:border-danger-900 dark:text-danger-500"
         role="alert" tabindex="-1" aria-labelledby="{{$id}}">
        <div class="flex">
            <div class="shrink-0">
                <svg class="shrink-0 size-4 mt-0.5" xmlns="http://www.w3.org/2000/svg" width="24"
                     height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                     stroke-width="2"
                     stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="12" r="10"></circle>
                    <path d="m15 9-6 6"></path>
                    <path d="m9 9 6 6"></path>
                </svg>
            </div>
            <div class="ms-4">
                <h3 id="{{$id}}" class="text-sm font-semibold">
                    {{__("Whoops! Something went wrong.")}}
                </h3>
                <div class="mt-2 text-sm text-danger-700 dark:text-danger-400">
                    <ul class="list-disc space-y-1 ps-5">
                        @foreach($errors->all() as $e)
                            <li>{{$e}}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
    </div>
@endif