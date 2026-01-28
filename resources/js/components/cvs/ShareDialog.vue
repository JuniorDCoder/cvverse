<script setup lang="ts">
import { Share2, Copy, Check, Trash2, Clock, Shield, AlertCircle, Loader2 } from 'lucide-vue-next';
import { ref, onMounted } from 'vue';
import ConfirmDeleteModal from '@/components/ConfirmDeleteModal.vue';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
    DialogTrigger,
} from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { useToast } from '@/composables/useToast';

interface Share {
    id: string;
    token: string;
    permission: 'view' | 'review' | 'edit';
    expires_at: string | null;
    is_active: boolean;
    created_at: string;
}

const props = defineProps<{
    cvId: number;
}>();

const { addToast } = useToast();
const shares = ref<Share[]>([]);
const isLoading = ref(false);
const isCreating = ref(false);
const copiedId = ref<string | null>(null);
const deleteShareId = ref<string | null>(null);
const isDeleteModalOpen = ref(false);

const newShare = ref({
    permission: 'view',
    expires_in: '7', // days
});

const permissionLabels = {
    view: 'View only',
    review: 'Review (Can comment)',
    edit: 'Edit (Can change content)',
};

const getShares = async () => {
    isLoading.value = true;
    try {
        const response = await fetch(`/cvs/${props.cvId}/shares`);
        const data = await response.json();
        if (data.success) {
            shares.value = data.shares;
        }
    } finally {
        isLoading.value = false;
    }
};

const createShare = async () => {
    isCreating.value = true;
    try {
        const expiresAt = new Date();
        expiresAt.setDate(expiresAt.getDate() + parseInt(newShare.value.expires_in));
        
        const response = await fetch(`/cvs/${props.cvId}/shares`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-XSRF-TOKEN': decodeURIComponent(document.cookie.split('; ').find(row => row.startsWith('XSRF-TOKEN='))?.split('=')[1] || ''),
            },
            body: JSON.stringify({
                permission: newShare.value.permission,
                expires_at: newShare.value.expires_in === '0' ? null : expiresAt.toISOString(),
            }),
        });
        const data = await response.json();
        if (data.success) {
            shares.value.unshift(data.share);
            addToast({
                title: 'Success',
                message: 'Share link created.',
                type: 'success'
            });
        }
    } finally {
        isCreating.value = false;
    }
};

const confirmDeleteShare = (shareId: string) => {
    deleteShareId.value = shareId;
    isDeleteModalOpen.value = true;
};

const deleteShare = async () => {
    if (!deleteShareId.value) return;
    
    const shareId = deleteShareId.value;
    try {
        const response = await fetch(`/cvs/${props.cvId}/shares/${shareId}`, {
            method: 'DELETE',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-XSRF-TOKEN': decodeURIComponent(document.cookie.split('; ').find(row => row.startsWith('XSRF-TOKEN='))?.split('=')[1] || ''),
            },
        });
        const data = await response.json();
        if (data.success) {
            shares.value = shares.value.filter(s => s.id !== shareId);
            addToast({
                title: 'Success',
                message: 'Share link deleted.',
                type: 'success'
            });
        }
    } catch (e) {
        console.error(e);
    } finally {
        deleteShareId.value = null;
        isDeleteModalOpen.value = false;
    }
};

const copyToClipboard = async (share: Share) => {
    const url = getFullUrl(share.token);
    await navigator.clipboard.writeText(url);
    copiedId.value = share.id;
    addToast({
        title: 'Copied',
        message: 'Share link copied to clipboard.',
        type: 'success'
    });
    setTimeout(() => {
        copiedId.value = null;
    }, 2000);
};

const getFullUrl = (token: string) => {
    return `${window.location.origin}/s/${token}`;
};

const formatDate = (dateString: string | null) => {
    if (!dateString) return 'Never';
    return new Date(dateString).toLocaleDateString('en-US', {
        month: 'short',
        day: 'numeric',
        year: 'numeric',
    });
};

onMounted(() => {
    getShares();
});
</script>

<template>
    <Dialog>
        <DialogTrigger as-child>
            <Button variant="outline" size="sm">
                <Share2 class="h-4 w-4 mr-2" />
                Share
            </Button>
        </DialogTrigger>
        <DialogContent class="max-w-[calc(100vw-2rem)] sm:max-w-[500px]">
            <DialogHeader>
                <DialogTitle>Share CV</DialogTitle>
                <DialogDescription>
                    Generate a shareable link with specific permissions.
                </DialogDescription>
            </DialogHeader>

            <div class="space-y-6 py-4">
                <!-- Create New Share -->
                <div class="grid gap-4 p-4 border rounded-lg bg-muted/30">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div class="space-y-2">
                            <Label>Permission</Label>
                            <Select v-model="newShare.permission">
                                <SelectTrigger>
                                    <SelectValue />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="view">View only</SelectItem>
                                    <SelectItem value="review">Review</SelectItem>
                                    <SelectItem value="edit">Edit</SelectItem>
                                </SelectContent>
                            </Select>
                        </div>
                        <div class="space-y-2">
                            <Label>Expires in</Label>
                            <Select v-model="newShare.expires_in">
                                <SelectTrigger>
                                    <SelectValue />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="1">1 Day</SelectItem>
                                    <SelectItem value="7">7 Days</SelectItem>
                                    <SelectItem value="30">30 Days</SelectItem>
                                    <SelectItem value="0">Never</SelectItem>
                                </SelectContent>
                            </Select>
                        </div>
                    </div>
                    <Button @click="createShare" :disabled="isCreating" class="w-full">
                        <Loader2 v-if="isCreating" class="h-4 w-4 mr-2 animate-spin" />
                        Create Link
                    </Button>
                </div>

                <!-- Existing Shares -->
                <div class="space-y-3">
                    <h4 class="text-sm font-medium flex items-center gap-2">
                        Active Links ({{ shares.length }})
                    </h4>
                    
                    <div v-if="isLoading" class="flex justify-center py-4">
                        <Loader2 class="h-6 w-6 animate-spin text-muted-foreground" />
                    </div>

                    <div v-else-if="shares.length === 0" class="text-center py-8 border rounded-lg border-dashed">
                        <AlertCircle class="h-8 w-8 mx-auto mb-2 text-muted-foreground opacity-50" />
                        <p class="text-sm text-muted-foreground">No active share links.</p>
                    </div>

                    <div v-else class="space-y-3 max-h-[300px] overflow-y-auto pr-1">
                        <div v-for="share in shares" :key="share.id" class="p-3 border rounded-lg space-y-2 group">
                            <div class="flex items-start justify-between">
                                <div class="space-y-1">
                                    <div class="flex items-center gap-2">
                                        <span class="text-xs font-semibold uppercase tracking-wider px-1.5 py-0.5 rounded bg-primary/10 text-primary">
                                            {{ share.permission }}
                                        </span>
                                        <span class="text-xs text-muted-foreground flex items-center gap-1">
                                            <Clock class="h-3 w-3" />
                                            Expires: {{ formatDate(share.expires_at) }}
                                        </span>
                                    </div>
                                    <p class="text-[10px] font-mono break-all text-muted-foreground bg-muted p-1.5 rounded-md border leading-relaxed">
                                        {{ getFullUrl(share.token) }}
                                    </p>
                                </div>
                                <div class="flex items-center gap-1">
                                    <Button variant="ghost" size="icon" class="h-8 w-8" @click="copyToClipboard(share)">
                                        <Check v-if="copiedId === share.id" class="h-4 w-4 text-green-500" />
                                        <Copy v-else class="h-4 w-4" />
                                    </Button>
                                    <Button variant="ghost" size="icon" class="h-8 w-8 text-destructive" @click="confirmDeleteShare(share.id)">
                                        <Trash2 class="h-4 w-4" />
                                    </Button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <DialogFooter class="sm:justify-start">
                <p class="text-[10px] text-muted-foreground flex items-center gap-1">
                    <Shield class="h-3 w-3" />
                    Anyone with the link can access the CV according to permission.
                </p>
            </DialogFooter>
        </DialogContent>
    </Dialog>

    <ConfirmDeleteModal
        v-model:open="isDeleteModalOpen"
        title="Delete Share Link"
        description="Are you sure you want to delete this share link? This action cannot be undone."
        @confirm="deleteShare"
    />
</template>
