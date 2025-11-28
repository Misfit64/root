<div class="p-4 border rounded bg-white dark:bg-gray-800 dark:border-gray-700 space-y-4">

    <h2 class="text-lg font-bold text-gray-900 dark:text-white">Edit Person</h2>

    {{-- Name Fields --}}
    <div class="grid grid-cols-2 gap-4">
        <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">First Name</label>
            <input type="text" wire:model="first_name" class="border border-gray-300 dark:border-gray-600 rounded p-2 w-full bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-blue-500 focus:border-blue-500">
            @error('first_name') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Last Name</label>
            <input type="text" wire:model="last_name" class="border border-gray-300 dark:border-gray-600 rounded p-2 w-full bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-blue-500 focus:border-blue-500">
            @error('last_name') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
        </div>
    </div>

    {{-- Gender --}}
    <div>
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Gender</label>
        <select wire:model="gender" class="border border-gray-300 dark:border-gray-600 rounded p-2 w-full bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-blue-500 focus:border-blue-500">
            <option value="">Select Gender</option>
            @foreach(App\Enums\Gender::cases() as $g)
                <option value="{{ $g->value }}">{{ $g->name }}</option>
            @endforeach
        </select>
        @error('gender') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
    </div>

    {{-- Dates --}}
    <div class="grid grid-cols-2 gap-4">
        <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Birth Date</label>
            <input type="date" wire:model="birth_date" class="border border-gray-300 dark:border-gray-600 rounded p-2 w-full bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-blue-500 focus:border-blue-500">
            @error('birth_date') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Death Date</label>
            <input type="date" wire:model="death_date" class="border border-gray-300 dark:border-gray-600 rounded p-2 w-full bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-blue-500 focus:border-blue-500">
            @error('death_date') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
        </div>
    </div>

    {{-- Notes --}}
    <div>
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Notes</label>
        <textarea wire:model="notes" class="border border-gray-300 dark:border-gray-600 rounded p-2 w-full bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-blue-500 focus:border-blue-500" rows="3"></textarea>
        @error('notes') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
    </div>

    {{-- Photo --}}
    <div x-data="{
        photoPreview: null,
        showCropper: false,
        cropper: null,
        isNewFile: false,
        currentPhotoUrl: '{{ $person->default_photo_url }}',
        init() {
            this.$watch('showCropper', value => {
                if (value) {
                    // Wait for modal to be visible
                    setTimeout(() => {
                        this.initOrUpdateCropper();
                    }, 100);
                }
            });
        },
        initOrUpdateCropper() {
            if (typeof Cropper === 'undefined') {
                alert('Image editor library not loaded. Please refresh the page.');
                return;
            }

            const image = this.$refs.imageToCrop;
            
            if (this.cropper) {
                this.cropper.replace(this.photoPreview);
            } else {
                this.cropper = new Cropper(image, {
                    aspectRatio: 1,
                    viewMode: 1,
                    dragMode: 'move',
                    autoCropArea: 1,
                    restore: false,
                    guides: false,
                    center: false,
                    highlight: false,
                    cropBoxMovable: false,
                    cropBoxResizable: false,
                    toggleDragModeOnDblclick: false,
                    background: false,
                    minContainerWidth: 300,
                    minContainerHeight: 300,
                });
            }
        },
        editCurrent() {
            this.photoPreview = this.currentPhotoUrl;
            this.isNewFile = false;
            this.showCropper = true;
        },
        triggerUpload() {
            this.$refs.photoInput.click();
        },
        handleFileSelect(event) {
            const file = event.target.files[0];
            event.target.value = ''; 
            
            if (file) {
                this.isNewFile = true;
                const reader = new FileReader();
                reader.onload = (e) => {
                    this.photoPreview = e.target.result;
                    this.showCropper = true;
                };
                reader.readAsDataURL(file);
            }
        },
        rotate(deg) {
            if (this.cropper) this.cropper.rotate(deg);
        },
        zoom(ratio) {
            if (this.cropper) this.cropper.zoom(ratio);
        },
        reset() {
            if (this.cropper) this.cropper.reset();
        },
        cropImage() {
            if (!this.cropper) return;
            
            const canvas = this.cropper.getCroppedCanvas({
                width: 300,
                height: 300,
            });
            
            canvas.toBlob((blob) => {
                const file = new File([blob], 'cropped.jpg', { type: 'image/jpeg' });
                @this.upload('photo', file, (uploadedFilename) => {
                    this.showCropper = false;
                    this.photoPreview = canvas.toDataURL();
                    this.currentPhotoUrl = this.photoPreview; 
                }, () => {
                    alert('Error uploading cropped image');
                });
            }, 'image/jpeg');
        }
    }">
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Photo</label>
        
        <div class="flex items-center gap-4 mt-2">
            <div class="relative inline-block group">
                <template x-if="photoPreview">
                    <img :src="photoPreview" class="h-24 w-24 rounded-full object-cover border dark:border-gray-600">
                </template>
                <template x-if="!photoPreview">
                    <img src="{{ $person->default_photo_url }}" class="h-24 w-24 rounded-full object-cover border dark:border-gray-600">
                </template>
                
                {{-- Edit Overlay --}}
                <button type="button" @click="editCurrent" class="absolute inset-0 flex items-center justify-center bg-black/40 rounded-full opacity-0 group-hover:opacity-100 transition-opacity cursor-pointer text-white" title="Edit Current Photo">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                    </svg>
                </button>
            </div>

            <div class="flex flex-col gap-2">
                <button type="button" @click="triggerUpload" class="px-3 py-2 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm text-sm font-medium text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-600 focus:outline-none">
                    Upload New Photo
                </button>
                <input x-ref="photoInput" type="file" class="hidden" accept="image/*" @change="handleFileSelect">
                <p class="text-xs text-gray-500 dark:text-gray-400">Click image to edit existing.</p>
            </div>
        </div>
        @error('photo') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror

        {{-- Cropper Modal --}}
        <template x-teleport="body">
            <div x-show="showCropper" style="display: none;" class="fixed inset-0 z-[100] overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
                <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                    <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true"></div>
                    <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
                    <div class="inline-block align-bottom bg-white dark:bg-gray-800 rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                        <div class="bg-white dark:bg-gray-800 px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                            <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                                <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-white" id="modal-title">
                                    Edit Photo
                                </h3>

                                {{-- Toolbar (Moved Top) --}}
                                <div class="mt-4 flex justify-center gap-2 relative z-10">
                                    <button type="button" @click="zoom(0.1)" class="p-2 bg-gray-200 dark:bg-gray-700 rounded hover:bg-gray-300 dark:hover:bg-gray-600" title="Zoom In">
                                        <svg class="w-5 h-5 text-gray-700 dark:text-gray-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0zM10 7v3m0 0v3m0-3h3m-3 0H7"></path></svg>
                                    </button>
                                    <button type="button" @click="zoom(-0.1)" class="p-2 bg-gray-200 dark:bg-gray-700 rounded hover:bg-gray-300 dark:hover:bg-gray-600" title="Zoom Out">
                                        <svg class="w-5 h-5 text-gray-700 dark:text-gray-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0zM13 10H7"></path></svg>
                                    </button>
                                    <button type="button" @click="rotate(-90)" class="p-2 bg-gray-200 dark:bg-gray-700 rounded hover:bg-gray-300 dark:hover:bg-gray-600" title="Rotate Left">
                                        <svg class="w-5 h-5 text-gray-700 dark:text-gray-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"></path></svg>
                                    </button>
                                    <button type="button" @click="rotate(90)" class="p-2 bg-gray-200 dark:bg-gray-700 rounded hover:bg-gray-300 dark:hover:bg-gray-600" title="Rotate Right">
                                        <svg class="w-5 h-5 text-gray-700 dark:text-gray-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 10H11a8 8 0 00-8 8v2m14-10l-6 6m6-6l-6-6"></path></svg>
                                    </button>
                                    <button type="button" @click="reset()" class="p-2 bg-gray-200 dark:bg-gray-700 rounded hover:bg-gray-300 dark:hover:bg-gray-600" title="Reset">
                                        <svg class="w-5 h-5 text-gray-700 dark:text-gray-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
                                    </button>
                                </div>
                                
                                {{-- Image Area --}}
                                <div class="mt-4 w-full h-80 bg-gray-100 dark:bg-gray-900 rounded flex items-center justify-center overflow-hidden relative" wire:ignore>
                                    <img x-ref="imageToCrop" :src="photoPreview" class="max-w-full max-h-full block">
                                </div>

                            </div>
                        </div>
                        <div class="bg-gray-50 dark:bg-gray-700 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                            <button type="button" @click="cropImage" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-blue-600 text-base font-medium text-white hover:bg-blue-700 focus:outline-none sm:ml-3 sm:w-auto sm:text-sm">
                                Apply Crop
                            </button>
                            <button type="button" @click="showCropper = false" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 dark:border-gray-600 shadow-sm px-4 py-2 bg-white dark:bg-gray-800 text-base font-medium text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                                Cancel
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </template>
        
        <style>
            /* Circular Crop Mask */
            .cropper-view-box,
            .cropper-face {
                border-radius: 50% !important;
            }
            
            /* Make the non-cropped area darker to highlight the circle */
            .cropper-modal {
                opacity: 0.8 !important;
                background-color: #000 !important;
            }

            /* Ensure the image fits */
            .cropper-container {
                width: 100%;
                height: 100%;
            }
        </style>
    </div>

    {{-- Actions --}}
    {{-- Actions --}}
    <div class="flex justify-between items-center pt-4 border-t dark:border-gray-700">
        <button
            class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 transition"
            wire:click="save"
        >
            Save Changes
        </button>

        <button
            class="px-4 py-2 bg-red-600 text-white rounded hover:bg-red-700 shadow-md hover:shadow-lg transition text-sm font-semibold"
            wire:click="$set('showDeleteConfirmation', true)"
        >
            Delete Profile
        </button>
    </div>

    {{-- Delete Confirmation Modal --}}
    @if($showDeleteConfirmation ?? false)
        <div class="fixed inset-0 bg-black/50 flex items-center justify-center z-[60]">
            <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-xl max-w-md w-full">
                <h3 class="text-lg font-bold text-red-600 dark:text-red-400 mb-4">Delete Profile?</h3>
                <p class="text-gray-700 dark:text-gray-300 mb-6">
                    Are you sure you want to delete <strong>{{ $person->full_name }}</strong>? 
                    This action cannot be undone and will remove them from all family trees and relationships.
                </p>
                <div class="flex justify-end gap-3">
                    <button 
                        wire:click="$set('showDeleteConfirmation', false)" 
                        class="px-4 py-2 bg-gray-200 dark:bg-gray-700 text-gray-800 dark:text-gray-200 rounded hover:bg-gray-300 dark:hover:bg-gray-600"
                    >
                        Cancel
                    </button>
                    <button 
                        wire:click="deletePerson" 
                        class="px-4 py-2 bg-red-600 text-white rounded hover:bg-red-700"
                    >
                        Yes, Delete
                    </button>
                </div>
            </div>
        </div>
    @endif

</div>
