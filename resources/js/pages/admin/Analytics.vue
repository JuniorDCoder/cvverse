<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import { BarChart3, CalendarDays, DollarSign, FileText, Target, TrendingUp, Users } from 'lucide-vue-next';
import { computed, ref } from 'vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import AppLayout from '@/layouts/AppLayout.vue';
import { analytics as adminAnalytics } from '@/routes/admin';

interface FilterOptions {
    periods: Array<{ value: string; label: string }>;
    currencies: string[];
}

interface Filters {
    period: string;
    currency: string;
    start_date: string | null;
    end_date: string | null;
}

interface Overview {
    new_signups: number;
    onboarding_completion_rate: number;
    total_cvs: number;
    total_cover_letters: number;
    total_applications: number;
    active_subscribers: number;
    new_paid_subscribers: number;
    estimated_mrr: number;
    estimated_arr: number;
    estimated_bookings: number;
    currency: string;
    currency_symbol: string;
}

interface GrowthPoint {
    date: string;
    label: string;
    count: number;
}

interface GrowthData {
    users: GrowthPoint[];
    cvs: GrowthPoint[];
    applications: GrowthPoint[];
}

interface StatusItem {
    status: string;
    label: string;
    count: number;
}

interface TemplateItem {
    slug: string;
    name: string;
    count: number;
}

interface IndustryItem {
    industry: string;
    label: string;
    count: number;
    percentage: number;
}

interface InterestItem {
    key: string;
    label: string;
    count: number;
    percentage: number;
}

interface CurrencyRevenueItem {
    currency: string;
    active_subscribers: number;
    mrr: number;
    arr: number;
    currency_symbol: string;
}

const props = defineProps<{
    filters: Filters;
    filterOptions: FilterOptions;
    dateRange: {
        label: string;
        start: string | null;
        end: string | null;
    };
    overview: Overview;
    growth: GrowthData;
    applicationsByStatus: StatusItem[];
    topTemplates: TemplateItem[];
    topIndustries: IndustryItem[];
    topInterests: InterestItem[];
    revenueByCurrency: CurrencyRevenueItem[];
}>();

const selectedPeriod = ref(props.filters.period || 'year');
const selectedCurrency = ref(props.filters.currency || 'ALL');
const startDate = ref(props.filters.start_date || '');
const endDate = ref(props.filters.end_date || '');

const isCustomPeriod = computed(() => selectedPeriod.value === 'custom');

const applyFilters = () => {
    router.get(
        adminAnalytics.url({
            query: {
                period: selectedPeriod.value,
                currency: selectedCurrency.value,
                start_date: isCustomPeriod.value ? startDate.value || undefined : undefined,
                end_date: isCustomPeriod.value ? endDate.value || undefined : undefined,
            },
        }),
        {},
        { preserveState: true, preserveScroll: true, replace: true }
    );
};

const maxUserGrowth = computed(() => Math.max(1, ...props.growth.users.map((point) => point.count)));
const maxCvGrowth = computed(() => Math.max(1, ...props.growth.cvs.map((point) => point.count)));
const maxApplicationGrowth = computed(() => Math.max(1, ...props.growth.applications.map((point) => point.count)));
const maxTemplateCount = computed(() => Math.max(1, ...props.topTemplates.map((item) => item.count)));
const maxStatusCount = computed(() => Math.max(1, ...props.applicationsByStatus.map((item) => item.count)));

const formatMoney = (amount: number, currency: string) => {
    if (currency === 'ALL') {
        return amount.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }

    return new Intl.NumberFormat(undefined, {
        style: 'currency',
        currency,
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
    }).format(amount);
};
</script>

<template>
    <AppLayout :breadcrumbs="[{ title: 'Admin', href: '/admin' }, { title: 'Analytics' }]">
        <Head title="Analytics" />

        <div class="flex h-full flex-1 flex-col gap-6 p-6">
            <div class="flex flex-col gap-3 md:flex-row md:items-end md:justify-between">
                <div>
                    <h1 class="text-2xl font-bold">Analytics</h1>
                    <p class="text-muted-foreground">Real-time platform insights with period and currency filters.</p>
                    <p class="text-xs text-muted-foreground mt-1">Range: {{ dateRange.label }}</p>
                </div>
                <div class="grid grid-cols-1 gap-2 md:grid-cols-5">
                    <select
                        v-model="selectedPeriod"
                        class="h-9 rounded-md border border-input bg-background px-3 text-sm"
                    >
                        <option v-for="period in filterOptions.periods" :key="period.value" :value="period.value">
                            {{ period.label }}
                        </option>
                    </select>

                    <select
                        v-model="selectedCurrency"
                        class="h-9 rounded-md border border-input bg-background px-3 text-sm"
                    >
                        <option v-for="currency in filterOptions.currencies" :key="currency" :value="currency">
                            {{ currency }}
                        </option>
                    </select>

                    <input
                        v-if="isCustomPeriod"
                        v-model="startDate"
                        type="date"
                        class="h-9 rounded-md border border-input bg-background px-3 text-sm"
                    />

                    <input
                        v-if="isCustomPeriod"
                        v-model="endDate"
                        type="date"
                        class="h-9 rounded-md border border-input bg-background px-3 text-sm"
                    />

                    <Button class="h-9" @click="applyFilters">
                        <CalendarDays class="mr-2 h-4 w-4" />
                        Apply
                    </Button>
                </div>
            </div>

            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                <Card>
                    <CardHeader class="pb-2">
                        <CardTitle class="text-sm font-medium">Estimated MRR</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div class="flex items-center justify-between">
                            <div class="text-2xl font-bold">{{ formatMoney(overview.estimated_mrr, overview.currency) }}</div>
                            <DollarSign class="h-5 w-5 text-muted-foreground" />
                        </div>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader class="pb-2">
                        <CardTitle class="text-sm font-medium">Estimated ARR</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div class="flex items-center justify-between">
                            <div class="text-2xl font-bold">{{ formatMoney(overview.estimated_arr, overview.currency) }}</div>
                            <TrendingUp class="h-5 w-5 text-muted-foreground" />
                        </div>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader class="pb-2">
                        <CardTitle class="text-sm font-medium">New Signups</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div class="flex items-center justify-between">
                            <div class="text-2xl font-bold">{{ overview.new_signups }}</div>
                            <Users class="h-5 w-5 text-muted-foreground" />
                        </div>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader class="pb-2">
                        <CardTitle class="text-sm font-medium">Active Subscribers</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div class="flex items-center justify-between">
                            <div class="text-2xl font-bold">{{ overview.active_subscribers }}</div>
                            <Target class="h-5 w-5 text-muted-foreground" />
                        </div>
                    </CardContent>
                </Card>
            </div>

            <div class="grid gap-6 lg:grid-cols-3">
                <Card>
                    <CardHeader>
                        <CardTitle>User Growth</CardTitle>
                        <CardDescription>New users per day in selected period</CardDescription>
                    </CardHeader>
                    <CardContent>
                        <div class="flex h-28 items-end gap-1">
                            <div
                                v-for="point in growth.users"
                                :key="`users-${point.date}`"
                                class="min-w-0 flex-1 rounded-t bg-blue-500/70"
                                :style="{ height: `${(point.count / maxUserGrowth) * 100}%` }"
                                :title="`${point.label}: ${point.count}`"
                            />
                        </div>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader>
                        <CardTitle>CV Growth</CardTitle>
                        <CardDescription>CVs created per day</CardDescription>
                    </CardHeader>
                    <CardContent>
                        <div class="flex h-28 items-end gap-1">
                            <div
                                v-for="point in growth.cvs"
                                :key="`cvs-${point.date}`"
                                class="min-w-0 flex-1 rounded-t bg-purple-500/70"
                                :style="{ height: `${(point.count / maxCvGrowth) * 100}%` }"
                                :title="`${point.label}: ${point.count}`"
                            />
                        </div>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader>
                        <CardTitle>Application Growth</CardTitle>
                        <CardDescription>Job applications created per day</CardDescription>
                    </CardHeader>
                    <CardContent>
                        <div class="flex h-28 items-end gap-1">
                            <div
                                v-for="point in growth.applications"
                                :key="`applications-${point.date}`"
                                class="min-w-0 flex-1 rounded-t bg-emerald-500/70"
                                :style="{ height: `${(point.count / maxApplicationGrowth) * 100}%` }"
                                :title="`${point.label}: ${point.count}`"
                            />
                        </div>
                    </CardContent>
                </Card>
            </div>

            <div class="grid gap-6 lg:grid-cols-2">
                <Card>
                    <CardHeader>
                        <CardTitle class="flex items-center gap-2">
                            <FileText class="h-5 w-5" />
                            Top Templates
                        </CardTitle>
                        <CardDescription>Actual CV usage by template</CardDescription>
                    </CardHeader>
                    <CardContent>
                        <div v-if="topTemplates.length === 0" class="text-sm text-muted-foreground">No template usage yet for this period.</div>
                        <div v-else class="space-y-3">
                            <div v-for="template in topTemplates" :key="template.slug" class="space-y-1.5">
                                <div class="flex items-center justify-between text-sm">
                                    <span class="truncate">{{ template.name }}</span>
                                    <span class="text-muted-foreground">{{ template.count }}</span>
                                </div>
                                <div class="h-2 overflow-hidden rounded-full bg-muted">
                                    <div
                                        class="h-full rounded-full bg-gradient-to-r from-primary to-blue-500"
                                        :style="{ width: `${(template.count / maxTemplateCount) * 100}%` }"
                                    />
                                </div>
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader>
                        <CardTitle class="flex items-center gap-2">
                            <BarChart3 class="h-5 w-5" />
                            Application Statuses
                        </CardTitle>
                        <CardDescription>Status distribution for filtered period</CardDescription>
                    </CardHeader>
                    <CardContent>
                        <div class="space-y-3">
                            <div v-for="status in applicationsByStatus" :key="status.status" class="space-y-1.5">
                                <div class="flex items-center justify-between text-sm">
                                    <span>{{ status.label }}</span>
                                    <span class="text-muted-foreground">{{ status.count }}</span>
                                </div>
                                <div class="h-2 overflow-hidden rounded-full bg-muted">
                                    <div
                                        class="h-full rounded-full bg-gradient-to-r from-amber-500 to-orange-500"
                                        :style="{ width: `${(status.count / maxStatusCount) * 100}%` }"
                                    />
                                </div>
                            </div>
                        </div>
                    </CardContent>
                </Card>
            </div>

            <div class="grid gap-6 lg:grid-cols-3">
                <Card class="lg:col-span-2">
                    <CardHeader>
                        <CardTitle>User Industries (Onboarding Data)</CardTitle>
                        <CardDescription>Based on industries selected during onboarding</CardDescription>
                    </CardHeader>
                    <CardContent>
                        <div v-if="topIndustries.length === 0" class="text-sm text-muted-foreground">No onboarding industry data available yet.</div>
                        <div v-else class="grid gap-3 sm:grid-cols-2">
                            <div v-for="industry in topIndustries" :key="industry.industry" class="rounded-md border p-3">
                                <div class="flex items-center justify-between">
                                    <p class="text-sm font-medium">{{ industry.label }}</p>
                                    <p class="text-xs text-muted-foreground">{{ industry.percentage }}%</p>
                                </div>
                                <p class="text-xs text-muted-foreground mt-1">{{ industry.count }} users</p>
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader>
                        <CardTitle>Top Interests</CardTitle>
                        <CardDescription>Onboarding goal selections</CardDescription>
                    </CardHeader>
                    <CardContent>
                        <div v-if="topInterests.length === 0" class="text-sm text-muted-foreground">No interest selections yet.</div>
                        <div v-else class="space-y-3">
                            <div v-for="interest in topInterests" :key="interest.key" class="flex items-center justify-between text-sm">
                                <span class="truncate pr-2">{{ interest.label }}</span>
                                <span class="text-muted-foreground">{{ interest.count }}</span>
                            </div>
                        </div>
                    </CardContent>
                </Card>
            </div>

            <Card>
                <CardHeader>
                    <CardTitle>Revenue by Currency</CardTitle>
                    <CardDescription>Active subscription value split by currency</CardDescription>
                </CardHeader>
                <CardContent>
                    <div v-if="revenueByCurrency.length === 0" class="text-sm text-muted-foreground">
                        No active subscription revenue data yet.
                    </div>
                    <div v-else class="overflow-x-auto">
                        <table class="w-full min-w-[520px] text-sm">
                            <thead>
                                <tr class="border-b text-left text-muted-foreground">
                                    <th class="px-2 py-2 font-medium">Currency</th>
                                    <th class="px-2 py-2 font-medium">Active Subscribers</th>
                                    <th class="px-2 py-2 font-medium">MRR</th>
                                    <th class="px-2 py-2 font-medium">ARR</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="row in revenueByCurrency" :key="row.currency" class="border-b">
                                    <td class="px-2 py-2 font-medium">{{ row.currency }}</td>
                                    <td class="px-2 py-2">{{ row.active_subscribers }}</td>
                                    <td class="px-2 py-2">{{ formatMoney(row.mrr, row.currency) }}</td>
                                    <td class="px-2 py-2">{{ formatMoney(row.arr, row.currency) }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>
