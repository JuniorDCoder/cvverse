<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import {
    Wallet, TrendingUp, TrendingDown, Users, ArrowDownToLine,
    Ban, CheckCircle2, Clock, AlertCircle, RefreshCw, Phone,
} from 'lucide-vue-next';
import { ref, computed, nextTick } from 'vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import AppLayout from '@/layouts/AppLayout.vue';
import { useToast } from '@/composables/useToast';

interface Balance {
    country: string;
    currency: string;
    provider: string;
    service_name: string;
    value: number;
}

interface Subscription {
    id: number;
    name: string;
    email: string;
    plan: string | null;
    plan_price: string | null;
    plan_currency: string | null;
    plan_interval: string | null;
    status: string;
    expires_at: string | null;
}

interface WithdrawalRecord {
    id: number;
    amount: string;
    currency: string;
    receiver: string;
    service: string;
    status: string;
    mesomb_reference: string | null;
    message: string | null;
    failure_reason: string | null;
    user_name: string | null;
    created_at: string;
}

interface Stats {
    total_subscribers: number;
    total_revenue: number;
    total_withdrawn: number;
    pending_withdrawals: number;
}

const props = defineProps<{
    balances: Balance[];
    subscriptions: Subscription[];
    withdrawals: WithdrawalRecord[];
    stats: Stats;
}>();

const { addToast } = useToast();

// Tabs
const activeTab = ref<'overview' | 'subscriptions' | 'withdrawals'>('overview');

// Withdraw dialog
const showWithdrawDialog = ref(false);
const showConfirmDialog = ref(false);
const withdrawing = ref(false);
const withdrawForm = ref({
    amount: '',
    receiver: '',
    service: 'MTN',
});
const withdrawErrors = ref<Record<string, string>>({});

const totalBalance = computed(() => {
    return props.balances.reduce((sum, b) => sum + (b.value || 0), 0);
});

const formatCurrency = (amount: number | string, currency = 'XAF') => {
    const num = typeof amount === 'string' ? parseFloat(amount) : amount;
    return new Intl.NumberFormat('fr-CM', { style: 'currency', currency }).format(num);
};

const formatDate = (dateStr: string | null) => {
    if (!dateStr) return '—';
    return new Date(dateStr).toLocaleDateString('en-US', {
        year: 'numeric', month: 'short', day: 'numeric',
        hour: '2-digit', minute: '2-digit',
    });
};

const statusVariant = (status: string) => {
    switch (status) {
        case 'active': case 'success': return 'default';
        case 'pending': return 'secondary';
        case 'failed': case 'expired': case 'cancelled': return 'destructive';
        default: return 'outline';
    }
};

const statusColor = (status: string) => {
    switch (status) {
        case 'active': case 'success': return 'bg-green-500/10 text-green-600 dark:text-green-400 hover:bg-green-500/20';
        case 'pending': return 'bg-yellow-500/10 text-yellow-600 dark:text-yellow-400 hover:bg-yellow-500/20';
        case 'failed': case 'expired': case 'cancelled': return 'bg-red-500/10 text-red-600 dark:text-red-400 hover:bg-red-500/20';
        default: return '';
    }
};

const openWithdrawDialog = () => {
    withdrawForm.value = { amount: '', receiver: '', service: 'MTN' };
    withdrawErrors.value = {};
    showWithdrawDialog.value = true;
};

const validateAndConfirm = () => {
    withdrawErrors.value = {};

    if (!withdrawForm.value.amount || parseFloat(withdrawForm.value.amount) < 100) {
        withdrawErrors.value.amount = 'Minimum withdrawal is 100 XAF';
    }

    if (!withdrawForm.value.receiver || !/^\+?\d{8,15}$/.test(withdrawForm.value.receiver.replace(/\s/g, ''))) {
        withdrawErrors.value.receiver = 'Enter a valid phone number';
    }

    if (Object.keys(withdrawErrors.value).length > 0) return;

    showWithdrawDialog.value = false;
    nextTick(() => {
        showConfirmDialog.value = true;
    });
};

const processWithdrawal = () => {
    withdrawing.value = true;

    router.post('/admin/finance/withdraw', {
        amount: parseFloat(withdrawForm.value.amount),
        receiver: withdrawForm.value.receiver.replace(/\s/g, ''),
        service: withdrawForm.value.service,
    }, {
        preserveScroll: true,
        onSuccess: () => {
            addToast({ type: 'success', message: 'Withdrawal processed successfully!' });
            showConfirmDialog.value = false;
        },
        onError: (errors) => {
            const message = errors.message || errors.amount || 'Withdrawal failed.';
            addToast({ type: 'error', message });
            showConfirmDialog.value = false;
        },
        onFinish: () => {
            withdrawing.value = false;
        },
    });
};

const refreshBalance = async () => {
    window.location.reload();
};

// Search for subscriptions
const subSearch = ref('');
const filteredSubscriptions = computed(() => {
    if (!subSearch.value) return props.subscriptions;
    const q = subSearch.value.toLowerCase();
    return props.subscriptions.filter(s =>
        s.name.toLowerCase().includes(q) ||
        s.email.toLowerCase().includes(q) ||
        (s.plan && s.plan.toLowerCase().includes(q))
    );
});
</script>

<template>
    <AppLayout :breadcrumbs="[{ title: 'Admin', href: '/admin' }, { title: 'Finance' }]">
        <Head title="Finance & Payments" />

        <div class="flex h-full flex-1 flex-col gap-6 p-6">
            <!-- Header -->
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold">Finance & Payments</h1>
                    <p class="text-muted-foreground">Manage MeSomb balance, subscriptions and withdrawals</p>
                </div>
                <div class="flex gap-2">
                    <Button variant="outline" size="sm" @click="refreshBalance">
                        <RefreshCw class="mr-2 h-4 w-4" />
                        Refresh
                    </Button>
                    <Button size="sm" @click="openWithdrawDialog">
                        <ArrowDownToLine class="mr-2 h-4 w-4" />
                        Withdraw Funds
                    </Button>
                </div>
            </div>

            <!-- Stats Cards -->
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
                <Card>
                    <CardContent class="flex items-center gap-4 p-6">
                        <div class="flex h-12 w-12 items-center justify-center rounded-lg bg-blue-500/10">
                            <Wallet class="h-6 w-6 text-blue-600 dark:text-blue-400" />
                        </div>
                        <div>
                            <p class="text-sm text-muted-foreground">MeSomb Balance</p>
                            <p class="text-2xl font-bold">{{ formatCurrency(totalBalance) }}</p>
                        </div>
                    </CardContent>
                </Card>
                <Card>
                    <CardContent class="flex items-center gap-4 p-6">
                        <div class="flex h-12 w-12 items-center justify-center rounded-lg bg-green-500/10">
                            <TrendingUp class="h-6 w-6 text-green-600 dark:text-green-400" />
                        </div>
                        <div>
                            <p class="text-sm text-muted-foreground">Total Revenue</p>
                            <p class="text-2xl font-bold">{{ formatCurrency(stats.total_revenue) }}</p>
                        </div>
                    </CardContent>
                </Card>
                <Card>
                    <CardContent class="flex items-center gap-4 p-6">
                        <div class="flex h-12 w-12 items-center justify-center rounded-lg bg-orange-500/10">
                            <TrendingDown class="h-6 w-6 text-orange-600 dark:text-orange-400" />
                        </div>
                        <div>
                            <p class="text-sm text-muted-foreground">Total Withdrawn</p>
                            <p class="text-2xl font-bold">{{ formatCurrency(stats.total_withdrawn) }}</p>
                        </div>
                    </CardContent>
                </Card>
                <Card>
                    <CardContent class="flex items-center gap-4 p-6">
                        <div class="flex h-12 w-12 items-center justify-center rounded-lg bg-purple-500/10">
                            <Users class="h-6 w-6 text-purple-600 dark:text-purple-400" />
                        </div>
                        <div>
                            <p class="text-sm text-muted-foreground">Active Subscribers</p>
                            <p class="text-2xl font-bold">{{ stats.total_subscribers }}</p>
                        </div>
                    </CardContent>
                </Card>
            </div>

            <!-- Balance Breakdown -->
            <Card v-if="balances.length > 0">
                <CardContent class="p-6">
                    <h2 class="mb-4 text-lg font-semibold">Balance by Provider</h2>
                    <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-3">
                        <div
                            v-for="(balance, idx) in balances"
                            :key="idx"
                            class="flex items-center justify-between rounded-lg border p-4 transition-colors hover:bg-muted/50"
                        >
                            <div class="flex items-center gap-3">
                                <div class="flex h-10 w-10 items-center justify-center rounded-full bg-primary/10">
                                    <Wallet class="h-5 w-5 text-primary" />
                                </div>
                                <div>
                                    <p class="font-medium">{{ balance.service_name || balance.provider }}</p>
                                    <p class="text-xs text-muted-foreground">{{ balance.country }}</p>
                                </div>
                            </div>
                            <p class="text-lg font-bold">{{ formatCurrency(balance.value, balance.currency) }}</p>
                        </div>
                    </div>
                </CardContent>
            </Card>

            <!-- Tabs -->
            <div class="flex gap-1 rounded-lg bg-muted p-1">
                <button
                    v-for="tab in (['overview', 'subscriptions', 'withdrawals'] as const)"
                    :key="tab"
                    class="flex-1 rounded-md px-4 py-2 text-sm font-medium capitalize transition-colors"
                    :class="activeTab === tab
                        ? 'bg-background text-foreground shadow-sm'
                        : 'text-muted-foreground hover:text-foreground'"
                    @click="activeTab = tab"
                >
                    {{ tab }}
                </button>
            </div>

            <!-- Overview Tab -->
            <template v-if="activeTab === 'overview'">
                <!-- Recent Subscriptions -->
                <Card>
                    <CardContent class="p-0">
                        <div class="flex items-center justify-between border-b p-4">
                            <h2 class="text-lg font-semibold">Recent Subscriptions</h2>
                            <Badge variant="outline">{{ subscriptions.length }} total</Badge>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="w-full">
                                <thead>
                                    <tr class="border-b bg-muted/50">
                                        <th class="p-4 text-left text-sm font-medium">User</th>
                                        <th class="p-4 text-left text-sm font-medium">Plan</th>
                                        <th class="p-4 text-left text-sm font-medium">Amount</th>
                                        <th class="p-4 text-left text-sm font-medium">Status</th>
                                        <th class="p-4 text-left text-sm font-medium">Expires</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr
                                        v-for="sub in subscriptions.slice(0, 5)"
                                        :key="sub.id"
                                        class="border-b last:border-0 transition-colors hover:bg-muted/50"
                                    >
                                        <td class="p-4">
                                            <div>
                                                <p class="font-medium">{{ sub.name }}</p>
                                                <p class="text-xs text-muted-foreground">{{ sub.email }}</p>
                                            </div>
                                        </td>
                                        <td class="p-4">{{ sub.plan || '—' }}</td>
                                        <td class="p-4">
                                            <span v-if="sub.plan_price">{{ formatCurrency(parseFloat(sub.plan_price), sub.plan_currency || 'XAF') }}</span>
                                            <span v-else class="text-muted-foreground">—</span>
                                        </td>
                                        <td class="p-4">
                                            <Badge :variant="statusVariant(sub.status)" :class="statusColor(sub.status)">
                                                {{ sub.status }}
                                            </Badge>
                                        </td>
                                        <td class="p-4 text-sm text-muted-foreground">{{ formatDate(sub.expires_at) }}</td>
                                    </tr>
                                    <tr v-if="subscriptions.length === 0">
                                        <td colspan="5" class="p-8 text-center text-muted-foreground">No subscriptions yet.</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <div v-if="subscriptions.length > 5" class="border-t p-3 text-center">
                            <Button variant="ghost" size="sm" @click="activeTab = 'subscriptions'">
                                View all {{ subscriptions.length }} subscriptions
                            </Button>
                        </div>
                    </CardContent>
                </Card>

                <!-- Recent Withdrawals -->
                <Card>
                    <CardContent class="p-0">
                        <div class="flex items-center justify-between border-b p-4">
                            <h2 class="text-lg font-semibold">Recent Withdrawals</h2>
                            <Badge variant="outline">{{ withdrawals.length }} total</Badge>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="w-full">
                                <thead>
                                    <tr class="border-b bg-muted/50">
                                        <th class="p-4 text-left text-sm font-medium">Amount</th>
                                        <th class="p-4 text-left text-sm font-medium">Receiver</th>
                                        <th class="p-4 text-left text-sm font-medium">Service</th>
                                        <th class="p-4 text-left text-sm font-medium">Status</th>
                                        <th class="p-4 text-left text-sm font-medium">Date</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr
                                        v-for="w in withdrawals.slice(0, 5)"
                                        :key="w.id"
                                        class="border-b last:border-0 transition-colors hover:bg-muted/50"
                                    >
                                        <td class="p-4 font-medium">{{ formatCurrency(parseFloat(w.amount), w.currency) }}</td>
                                        <td class="p-4">
                                            <div class="flex items-center gap-2">
                                                <Phone class="h-3.5 w-3.5 text-muted-foreground" />
                                                <span class="font-mono text-sm">{{ w.receiver }}</span>
                                            </div>
                                        </td>
                                        <td class="p-4">
                                            <Badge variant="outline">{{ w.service }}</Badge>
                                        </td>
                                        <td class="p-4">
                                            <Badge :variant="statusVariant(w.status)" :class="statusColor(w.status)">
                                                {{ w.status }}
                                            </Badge>
                                        </td>
                                        <td class="p-4 text-sm text-muted-foreground">{{ formatDate(w.created_at) }}</td>
                                    </tr>
                                    <tr v-if="withdrawals.length === 0">
                                        <td colspan="5" class="p-8 text-center text-muted-foreground">No withdrawals yet.</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <div v-if="withdrawals.length > 5" class="border-t p-3 text-center">
                            <Button variant="ghost" size="sm" @click="activeTab = 'withdrawals'">
                                View all {{ withdrawals.length }} withdrawals
                            </Button>
                        </div>
                    </CardContent>
                </Card>
            </template>

            <!-- Subscriptions Tab -->
            <template v-if="activeTab === 'subscriptions'">
                <Card>
                    <CardContent class="p-0">
                        <div class="flex flex-col gap-4 border-b p-4 sm:flex-row sm:items-center sm:justify-between">
                            <h2 class="text-lg font-semibold">All Subscriptions</h2>
                            <div class="relative w-full max-w-xs">
                                <Input
                                    v-model="subSearch"
                                    placeholder="Search by name, email, plan..."
                                    class="pl-9"
                                />
                            </div>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="w-full">
                                <thead>
                                    <tr class="border-b bg-muted/50">
                                        <th class="p-4 text-left text-sm font-medium">User</th>
                                        <th class="p-4 text-left text-sm font-medium">Plan</th>
                                        <th class="p-4 text-left text-sm font-medium">Amount</th>
                                        <th class="p-4 text-left text-sm font-medium">Interval</th>
                                        <th class="p-4 text-left text-sm font-medium">Status</th>
                                        <th class="p-4 text-left text-sm font-medium">Expires</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr
                                        v-for="sub in filteredSubscriptions"
                                        :key="sub.id"
                                        class="border-b last:border-0 transition-colors hover:bg-muted/50"
                                    >
                                        <td class="p-4">
                                            <div>
                                                <p class="font-medium">{{ sub.name }}</p>
                                                <p class="text-xs text-muted-foreground">{{ sub.email }}</p>
                                            </div>
                                        </td>
                                        <td class="p-4">{{ sub.plan || '—' }}</td>
                                        <td class="p-4">
                                            <span v-if="sub.plan_price">{{ formatCurrency(parseFloat(sub.plan_price), sub.plan_currency || 'XAF') }}</span>
                                            <span v-else class="text-muted-foreground">—</span>
                                        </td>
                                        <td class="p-4 capitalize">{{ sub.plan_interval?.replace('_', ' ') || '—' }}</td>
                                        <td class="p-4">
                                            <Badge :variant="statusVariant(sub.status)" :class="statusColor(sub.status)">
                                                {{ sub.status }}
                                            </Badge>
                                        </td>
                                        <td class="p-4 text-sm text-muted-foreground">{{ formatDate(sub.expires_at) }}</td>
                                    </tr>
                                    <tr v-if="filteredSubscriptions.length === 0">
                                        <td colspan="6" class="p-8 text-center text-muted-foreground">
                                            {{ subSearch ? 'No matching subscriptions found.' : 'No subscriptions yet.' }}
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </CardContent>
                </Card>
            </template>

            <!-- Withdrawals Tab -->
            <template v-if="activeTab === 'withdrawals'">
                <Card>
                    <CardContent class="p-0">
                        <div class="flex items-center justify-between border-b p-4">
                            <h2 class="text-lg font-semibold">Withdrawal History</h2>
                            <Button size="sm" @click="openWithdrawDialog">
                                <ArrowDownToLine class="mr-2 h-4 w-4" />
                                New Withdrawal
                            </Button>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="w-full">
                                <thead>
                                    <tr class="border-b bg-muted/50">
                                        <th class="p-4 text-left text-sm font-medium">Amount</th>
                                        <th class="p-4 text-left text-sm font-medium">Receiver</th>
                                        <th class="p-4 text-left text-sm font-medium">Service</th>
                                        <th class="p-4 text-left text-sm font-medium">Status</th>
                                        <th class="p-4 text-left text-sm font-medium">Reference</th>
                                        <th class="p-4 text-left text-sm font-medium">Initiated By</th>
                                        <th class="p-4 text-left text-sm font-medium">Date</th>
                                        <th class="p-4 text-left text-sm font-medium">Details</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr
                                        v-for="w in withdrawals"
                                        :key="w.id"
                                        class="border-b last:border-0 transition-colors hover:bg-muted/50"
                                    >
                                        <td class="p-4 font-semibold">{{ formatCurrency(parseFloat(w.amount), w.currency) }}</td>
                                        <td class="p-4">
                                            <div class="flex items-center gap-2">
                                                <Phone class="h-3.5 w-3.5 text-muted-foreground" />
                                                <span class="font-mono text-sm">{{ w.receiver }}</span>
                                            </div>
                                        </td>
                                        <td class="p-4">
                                            <Badge variant="outline">{{ w.service }}</Badge>
                                        </td>
                                        <td class="p-4">
                                            <Badge :variant="statusVariant(w.status)" :class="statusColor(w.status)">
                                                <CheckCircle2 v-if="w.status === 'success'" class="mr-1 h-3 w-3" />
                                                <Clock v-else-if="w.status === 'pending'" class="mr-1 h-3 w-3" />
                                                <Ban v-else class="mr-1 h-3 w-3" />
                                                {{ w.status }}
                                            </Badge>
                                        </td>
                                        <td class="p-4">
                                            <span v-if="w.mesomb_reference" class="font-mono text-xs text-muted-foreground">
                                                {{ w.mesomb_reference.slice(0, 12) }}...
                                            </span>
                                            <span v-else class="text-muted-foreground">—</span>
                                        </td>
                                        <td class="p-4 text-sm">{{ w.user_name || '—' }}</td>
                                        <td class="p-4 text-sm text-muted-foreground">{{ formatDate(w.created_at) }}</td>
                                        <td class="p-4 text-sm">
                                            <span v-if="w.failure_reason" class="text-red-500">{{ w.failure_reason }}</span>
                                            <span v-else-if="w.message" class="text-muted-foreground">{{ w.message }}</span>
                                            <span v-else class="text-muted-foreground">—</span>
                                        </td>
                                    </tr>
                                    <tr v-if="withdrawals.length === 0">
                                        <td colspan="8" class="p-8 text-center text-muted-foreground">
                                            No withdrawals recorded yet.
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </CardContent>
                </Card>
            </template>
        </div>

        <!-- Withdraw Form Dialog -->
        <Dialog v-model:open="showWithdrawDialog">
            <DialogContent class="sm:max-w-md">
                <DialogHeader>
                    <DialogTitle class="flex items-center gap-2">
                        <ArrowDownToLine class="h-5 w-5" />
                        Withdraw Funds
                    </DialogTitle>
                    <DialogDescription>
                        Transfer funds from your MeSomb account to a mobile money number.
                    </DialogDescription>
                </DialogHeader>

                <div class="grid gap-4 py-4">
                    <div class="grid gap-2">
                        <Label for="amount">Amount (XAF)</Label>
                        <Input
                            id="amount"
                            v-model="withdrawForm.amount"
                            type="number"
                            min="100"
                            placeholder="e.g. 5000"
                            :class="{ 'border-red-500': withdrawErrors.amount }"
                        />
                        <p v-if="withdrawErrors.amount" class="text-xs text-red-500">{{ withdrawErrors.amount }}</p>
                    </div>
                    <div class="grid gap-2">
                        <Label for="receiver">Receiver Phone Number</Label>
                        <Input
                            id="receiver"
                            v-model="withdrawForm.receiver"
                            type="tel"
                            placeholder="e.g. 237670000000"
                            :class="{ 'border-red-500': withdrawErrors.receiver }"
                        />
                        <p v-if="withdrawErrors.receiver" class="text-xs text-red-500">{{ withdrawErrors.receiver }}</p>
                    </div>
                    <div class="grid gap-2">
                        <Label>Mobile Money Service</Label>
                        <Select v-model="withdrawForm.service">
                            <SelectTrigger>
                                <SelectValue placeholder="Select service" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value="MTN">MTN Mobile Money</SelectItem>
                                <SelectItem value="ORANGE">Orange Money</SelectItem>
                                <SelectItem value="AIRTEL">Airtel Money</SelectItem>
                            </SelectContent>
                        </Select>
                    </div>
                </div>

                <DialogFooter>
                    <Button variant="outline" @click="showWithdrawDialog = false">Cancel</Button>
                    <Button @click="validateAndConfirm">Continue</Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>

        <!-- Confirmation Dialog -->
        <Dialog v-model:open="showConfirmDialog">
            <DialogContent class="sm:max-w-md">
                <DialogHeader>
                    <DialogTitle class="flex items-center gap-2 text-orange-600 dark:text-orange-400">
                        <AlertCircle class="h-5 w-5" />
                        Confirm Withdrawal
                    </DialogTitle>
                    <DialogDescription>
                        Please review the details carefully. This action cannot be undone.
                    </DialogDescription>
                </DialogHeader>

                <div class="rounded-lg border bg-muted/30 p-4">
                    <div class="space-y-3">
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-muted-foreground">Amount</span>
                            <span class="text-lg font-bold">{{ formatCurrency(parseFloat(withdrawForm.amount || '0')) }}</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-muted-foreground">Receiver</span>
                            <span class="font-mono">{{ withdrawForm.receiver }}</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-muted-foreground">Service</span>
                            <Badge variant="outline">{{ withdrawForm.service }}</Badge>
                        </div>
                    </div>
                </div>

                <div class="flex items-start gap-2 rounded-lg border border-orange-200 bg-orange-50 p-3 dark:border-orange-900 dark:bg-orange-950/50">
                    <AlertCircle class="mt-0.5 h-4 w-4 shrink-0 text-orange-600 dark:text-orange-400" />
                    <p class="text-sm text-orange-700 dark:text-orange-300">
                        This will immediately transfer <strong>{{ formatCurrency(parseFloat(withdrawForm.amount || '0')) }}</strong>
                        from your MeSomb balance to <strong>{{ withdrawForm.receiver }}</strong> via <strong>{{ withdrawForm.service }}</strong>.
                    </p>
                </div>

                <DialogFooter>
                    <Button variant="outline" :disabled="withdrawing" @click="showConfirmDialog = false">
                        Cancel
                    </Button>
                    <Button
                        variant="default"
                        :disabled="withdrawing"
                        class="bg-orange-600 text-white hover:bg-orange-700 dark:bg-orange-600 dark:hover:bg-orange-700"
                        @click="processWithdrawal"
                    >
                        <template v-if="withdrawing">
                            <RefreshCw class="mr-2 h-4 w-4 animate-spin" />
                            Processing...
                        </template>
                        <template v-else>
                            Confirm Withdrawal
                        </template>
                    </Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>
    </AppLayout>
</template>
