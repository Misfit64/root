<?php

namespace App\Livewire\Tree;

use Livewire\Component;
use Livewire\Attributes\Layout;
use App\Models\User;
use App\Models\FamilyTree;
use Illuminate\Support\Str;

#[Layout('components.layouts.tree')]
class TreeIndex extends Component
{
    public $name = '';
    public $description = '';
    public $editingTreeId = null;
    
    // Sharing
    public $activeTab = 'details'; // details, members, invite, transfer
    public $invites = []; // [['email' => '', 'role' => 'viewer']]
    public $selectedNewOwnerId = null;
    public $showTransferConfirmation = false;

    public function mount()
    {
        $this->invites = [['email' => '', 'role' => 'viewer']];
    }

    public function updatedInvites()
    {
        // Auto-add new field if the last one has an email
        $lastInvite = end($this->invites);
        if (!empty($lastInvite['email'])) {
            $this->invites[] = ['email' => '', 'role' => 'viewer'];
        }
    }

    public function create()
    {
        $this->reset(['name', 'description', 'editingTreeId', 'activeTab', 'selectedNewOwnerId', 'showTransferConfirmation']);
        $this->invites = [['email' => '', 'role' => 'viewer']];
    }

    public function edit($id)
    {
        $tree = FamilyTree::where('user_id', auth()->id())->with('members')->findOrFail($id);
        $this->editingTreeId = $tree->id;
        $this->name = $tree->name;
        $this->description = $tree->description;
        $this->activeTab = 'details';
        $this->invites = [['email' => '', 'role' => 'viewer']];
        $this->selectedNewOwnerId = null;
        $this->showTransferConfirmation = false;
    }

    public function save()
    {
        $this->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'invites.*.email' => 'nullable|email|distinct', // Removed exists:users,email to allow inviting non-users (future) or just keep it strict for now? User said "add access to other user", implying existing users. Let's keep strict for now but per row.
            'invites.*.role' => 'required|in:viewer,editor',
        ]);

        // Validate emails exist if provided
        foreach ($this->invites as $index => $invite) {
            if (!empty($invite['email'])) {
                $user = User::where('email', $invite['email'])->first();
                if (!$user) {
                    $this->addError("invites.{$index}.email", "User not found.");
                    return;
                }
            }
        }

        $tree = null;

        if ($this->editingTreeId) {
            $tree = FamilyTree::where('user_id', auth()->id())->findOrFail($this->editingTreeId);
            $tree->update([
                'name' => $this->name,
                'description' => $this->description,
            ]);
            $this->dispatch('tree-updated');
        } else {
            $tree = FamilyTree::create([
                'user_id' => auth()->id(),
                'name' => $this->name,
                'description' => $this->description,
                'slug' => Str::slug($this->name) . '-' . Str::random(6),
            ]);
            $this->dispatch('tree-created');
        }

        // Process Invites
        foreach ($this->invites as $invite) {
            if (!empty($invite['email'])) {
                $userToShare = User::where('email', $invite['email'])->first();
                if ($userToShare && $userToShare->id !== auth()->id()) {
                    $tree->members()->syncWithoutDetaching([
                        $userToShare->id => ['role' => $invite['role']]
                    ]);
                }
            }
        }

        $this->dispatch('tree-saved');
        $this->create(); // Reset form
    }

    public function updateMemberRole($userId, $newRole)
    {
        if ($this->editingTreeId) {
            $tree = FamilyTree::where('user_id', auth()->id())->findOrFail($this->editingTreeId);
            $tree->members()->updateExistingPivot($userId, ['role' => $newRole]);
            $this->dispatch('tree-updated');
        }
    }

    public function removeMember($userId)
    {
        if ($this->editingTreeId) {
            $tree = FamilyTree::where('user_id', auth()->id())->findOrFail($this->editingTreeId);
            $tree->members()->detach($userId);
            $this->dispatch('tree-updated');
        }
    }

    public function confirmTransfer()
    {
        $this->validate([
            'selectedNewOwnerId' => 'required|exists:users,id'
        ]);
        $this->showTransferConfirmation = true;
    }

    public function cancelTransfer()
    {
        $this->showTransferConfirmation = false;
    }

    public function transferOwnership()
    {
        $this->validate([
            'selectedNewOwnerId' => 'required|exists:users,id'
        ]);

        if ($this->editingTreeId && $this->selectedNewOwnerId) {
            $tree = FamilyTree::where('user_id', auth()->id())->findOrFail($this->editingTreeId);
            
            // Verify the selected user is actually a member (optional but good for security)
            // Although the select list only shows members.
            
            $newOwnerId = $this->selectedNewOwnerId;

            // 1. Add current owner as editor
            $tree->members()->attach(auth()->id(), ['role' => 'editor']);

            // 2. Remove new owner from members
            $tree->members()->detach($newOwnerId);

            // 3. Update tree owner
            $tree->update(['user_id' => $newOwnerId]);

            $this->dispatch('tree-updated');
            $this->dispatch('tree-saved'); // Close modal
            $this->create(); // Reset state
        }
    }

    public function delete($id)
    {
        $tree = FamilyTree::where('user_id', auth()->id())->findOrFail($id);
        $tree->delete();
    }

    public function render()
    {
        $editingTreeMembers = [];
        if ($this->editingTreeId) {
            $tree = FamilyTree::where('user_id', auth()->id())->with('members')->find($this->editingTreeId);
            if ($tree) {
                $editingTreeMembers = $tree->members;
            }
        }

        return view('livewire.tree.tree-index', [
            'trees' => FamilyTree::where('user_id', auth()->id())->latest()->get(),
            'sharedTrees' => auth()->user()->sharedTrees()->with('owner')->latest()->get(),
            'editingTreeMembers' => $editingTreeMembers,
        ]);
    }
}
