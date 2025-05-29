<x-app-layout>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @forelse ($posts as $post)
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 flex flex-col">
                        <div class="flex items-center justify-between mb-4">
                            <div class="font-bold text-lg">{{ $post->user->name }}</div>
                            <div class="ml-auto text-sm text-gray-500">{{ $post->created_at->diffForHumans() }}</div>
                        </div>

                        @if ($post->image_path)
                            <div class="mb-4">
                                <img src="{{ asset('storage/' . $post->image_path) }}" alt="Post Image"
                                    class="w-full h-48 object-cover rounded-md">
                            </div>
                        @endif

                        @if ($post->caption)
                            <p class="text-gray-700 mb-4">{{ $post->caption }}</p>
                        @endif

                        <div class="flex items-center mt-auto">
                            <form
                                action="{{ $post->isLikedByUser(Auth::user()) ? route('posts.unlike', $post) : route('posts.like', $post) }}"
                                method="POST">
                                @csrf
                                @if ($post->isLikedByUser(Auth::user()))
                                    @method('DELETE')
                                    <button type="submit"
                                        class="flex items-center text-red-500 hover:text-red-700 mr-4">
                                        <x-icons.heart
                                            class="{{ $post->is_liked ? 'text-red-500' : 'text-gray-500' }}"></x-icons.heart>
                                        <span class="ml-1">{{ $post->likes->count() }}</span>
                                    </button>
                                @else
                                    <button type="submit"
                                        class="flex items-center text-gray-500 hover:text-gray-700 mr-4">
                                        <x-icons.heart-outlined
                                            class="{{ $post->is_liked ? 'text-red-500' : 'text-gray-500' }}">
                                        </x-icons.heart-outlined>
                                        <span class="ml-1">{{ $post->likes->count() }}</span>
                                    </button>
                                @endif
                            </form>

                            <button x-data="{}"
                                x-on:click="$dispatch('open-modal', { postId: {{ $post->id }} })"
                                class="flex items-center text-gray-500 hover:text-gray-700">
                                <x-icons.comment class="text-gray-500"></x-icons.comment>
                                <span class="ml-1">{{ $post->comments->count() }}</span>
                            </button>
                        </div>
                    </div>
                @empty
                    <p class="text-gray-600 col-span-full text-center">No Post.</p>
                @endforelse
            </div>
        </div>
    </div>

    {{-- Modal --}}
    <div x-data="{
        open: false,
        postId: null,
        comments: [],
        loading: false,
        async fetchComments(postId) {
            this.loading = true;
            try {
                const response = await fetch(`/posts/${postId}/comments`);
                const data = await response.json();
                this.comments = data;
            } catch (error) {
                console.error('Error fetching comments:', error);
                this.comments = [];
            } finally {
                this.loading = false;
            }
        }
    }"
        x-on:open-modal.window="
    open = true;
    postId = $event.detail.postId;
    fetchComments(postId);
    "
        x-show="open" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4" style="display: none;">

        <!-- Background overlay -->
        <div class="fixed inset-0 bg-black bg-opacity-50 transition-opacity" x-on:click="open = false"></div>

        <!-- Modal content -->
        <div class="relative bg-white rounded-lg shadow-xl z-10" style="width: 800px; height: 600px;"
            x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0 transform scale-95"
            x-transition:enter-end="opacity-100 transform scale-100"
            x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="opacity-100 transform scale-100"
            x-transition:leave-end="opacity-0 transform scale-95" x-on:click.stop>

            <!-- Modal header -->
            <div class="flex items-center justify-between p-4 border-b border-gray-200">
                <h3 class="text-xl font-semibold text-gray-900">
                    Comments
                </h3>
                <button x-on:click="open = false"
                    class="text-gray-400 hover:text-gray-600 transition-colors p-2 hover:bg-gray-100 rounded-full">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12">
                        </path>
                    </svg>
                </button>
            </div>

            <!-- Modal body -->
            <div class="p-6 overflow-y-auto" style="height: calc(600px - 140px);">
                <!-- Loading state -->
                <div x-show="loading" class="text-center py-8">
                    <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-gray-900"></div>
                    <p class="mt-2 text-gray-500">Loading...</p>
                </div>

                <!-- Comments list -->
                <div x-show="!loading">
                    <div class="space-y-4">
                        <template x-for="comment in comments" :key="comment.id">
                            <div class="flex space-x-3 p-4 bg-gray-50 rounded-lg">
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center space-x-2">
                                        <p class="text-sm font-medium text-gray-900" x-text="comment.user.name"></p>
                                        <p class="text-xs text-gray-500"
                                            x-text="new Date(comment.created_at).toLocaleDateString('id-ID', { 
                                            year: 'numeric', 
                                            month: 'short', 
                                            day: 'numeric',
                                            hour: '2-digit',
                                            minute: '2-digit'
                                        })">
                                        </p>
                                    </div>
                                    <p class="text-sm text-gray-700 mt-1" x-text="comment.comment"></p>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>
            </div>

            <!-- Modal footer -->
            <div class="items-center p-4 border-t border-gray-200">
                <form x-data="{
                    comment: '',
                    submitting: false,
                    async submitComment() {
                        if (!this.comment.trim()) return;
                
                        this.submitting = true;
                        try {
                            const response = await fetch(`/posts/${postId}/comments`, {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').getAttribute('content')
                                },
                                body: JSON.stringify({ comment: this.comment })
                            });
                
                            const data = await response.json();
                            if (data.success) {
                                comments.unshift(data.comment);
                                this.comment = '';
                            }
                        } catch (error) {
                            console.error('Error submitting comment:', error);
                        } finally {
                            this.submitting = false;
                        }
                    }
                }" x-on:submit.prevent="submitComment()">
                    <div class="flex space-x-3">
                        <div class="flex-1">
                            <input type="text" x-model="comment" placeholder="Write a comment..."
                                class="w-full p-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                :disabled="submitting">
                        </div>

                        <x-primary-button type="submit">Send</x-primary-button>
                    </div>
                </form>
            </div>
        </div>
    </div>

</x-app-layout>
