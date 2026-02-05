<script setup lang="ts">
import { Head, router, Link } from '@inertiajs/vue3';
import { 
    Save, 
    Eye, 
    Palette, 
    Type, 
    Layout, 
    GripVertical,
    Plus,
    Trash2,
    Settings,
    ChevronLeft,
    Image as ImageIcon,
    Code,
    Undo,
    Redo,
    Copy,
} from 'lucide-vue-next';
import { ref, computed, watch, onMounted } from 'vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle, CardDescription } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Switch } from '@/components/ui/switch';
import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/components/ui/tabs';
import { Textarea } from '@/components/ui/textarea';
import AppLayout from '@/layouts/AppLayout.vue';
import { useToast } from '@/composables/useToast';
import { dashboard as adminDashboard, templates as adminTemplates } from '@/routes/admin';
import { store as templateStore, update as templateUpdate, edit as templateEdit } from '@/routes/admin/template-builder';

interface Section {
    id: string;
    name: string;
    enabled: boolean;
    order: number;
}

interface Styles {
    primaryColor: string;
    secondaryColor: string;
    backgroundColor: string;
    textColor: string;
    headingColor: string;
    accentColor: string;
    fontFamily: string;
    headingFont: string;
    fontSize: string;
    lineHeight: string;
    spacing: string;
    borderRadius: string;
}

interface Layout {
    columns: number;
    headerStyle: string;
    sidebarPosition: string;
    sectionStyle: string;
}

interface Template {
    id?: number;
    name: string;
    slug?: string;
    description: string;
    category: string;
    is_premium: boolean;
    price: number | undefined;
    currency: string;
    is_active: boolean;
    layout: Layout;
    styles: Styles;
    sections: Section[];
    html_content: string;
    css_content: string;
    image?: string;
    thumbnail?: string;
}

const props = defineProps<{
    template: Template | null;
    categories: Record<string, string>;
    defaultStyles: Styles;
    defaultSections: Section[];
    defaultLayout: Layout;
}>();

const { addToast } = useToast();
const saving = ref(false);
const activeTab = ref('design');
const previewMode = ref(false);
const draggedSection = ref<number | null>(null);

// Image handling
const imageFile = ref<File | null>(null);
const imagePreview = ref<string | null>(props.template?.image ? `/storage/${props.template.image}` : null);
const imageInput = ref<HTMLInputElement | null>(null);

// Form state
const form = ref<Template>({
    name: props.template?.name || '',
    description: props.template?.description || '',
    category: props.template?.category || 'professional',
    is_premium: props.template?.is_premium || false,
    price: props.template?.price ?? undefined,
    currency: props.template?.currency || 'USD',
    is_active: props.template?.is_active ?? true,
    layout: props.template?.layout || { ...props.defaultLayout },
    styles: props.template?.styles || { ...props.defaultStyles },
    sections: props.template?.sections || [...props.defaultSections],
    html_content: props.template?.html_content || '',
    css_content: props.template?.css_content || '',
});

const isEditing = computed(() => !!props.template?.id);

const fonts = [
    { value: 'Inter, sans-serif', label: 'Inter' },
    { value: 'Arial, sans-serif', label: 'Arial' },
    { value: 'Georgia, serif', label: 'Georgia' },
    { value: 'Times New Roman, serif', label: 'Times New Roman' },
    { value: 'Roboto, sans-serif', label: 'Roboto' },
    { value: 'Open Sans, sans-serif', label: 'Open Sans' },
    { value: 'Lato, sans-serif', label: 'Lato' },
    { value: 'Montserrat, sans-serif', label: 'Montserrat' },
];

const headerStyles = [
    { value: 'centered', label: 'Centered' },
    { value: 'left', label: 'Left Aligned' },
    { value: 'split', label: 'Split (Name Left, Contact Right)' },
];

const sectionStyles = [
    { value: 'simple', label: 'Simple' },
    { value: 'boxed', label: 'Boxed' },
    { value: 'underlined', label: 'Underlined' },
    { value: 'accent', label: 'With Accent' },
];

// Drag and drop handlers
const handleDragStart = (index: number) => {
    draggedSection.value = index;
};

const handleDragOver = (e: DragEvent) => {
    e.preventDefault();
};

const handleDrop = (targetIndex: number) => {
    if (draggedSection.value === null || draggedSection.value === targetIndex) return;
    
    const sections = [...form.value.sections];
    const [removed] = sections.splice(draggedSection.value, 1);
    sections.splice(targetIndex, 0, removed);
    
    // Update order
    sections.forEach((section, index) => {
        section.order = index;
    });
    
    form.value.sections = sections;
    draggedSection.value = null;
};

const toggleSection = (index: number) => {
    form.value.sections[index].enabled = !form.value.sections[index].enabled;
};

// Image handling
const handleImageSelect = (event: Event) => {
    const input = event.target as HTMLInputElement;
    if (input.files && input.files[0]) {
        imageFile.value = input.files[0];
        imagePreview.value = URL.createObjectURL(input.files[0]);
    }
};

const removeImage = () => {
    imageFile.value = null;
    imagePreview.value = null;
    if (imageInput.value) {
        imageInput.value.value = '';
    }
};

// Sample CV data for preview
const sampleData = {
    personal_info: {
        full_name: 'John Doe',
        email: 'john.doe@example.com',
        phone: '+1 (555) 123-4567',
        location: 'New York, NY',
    },
    summary: 'Experienced software engineer with 8+ years of expertise in full-stack development.',
    experience: [
        {
            title: 'Senior Software Engineer',
            company: 'Tech Corp',
            location: 'San Francisco, CA',
            start_date: 'Jan 2020',
            end_date: 'Present',
            description: 'Led development of microservices architecture.',
        },
    ],
    education: [
        {
            degree: 'B.S. Computer Science',
            institution: 'MIT',
            graduation_date: '2017',
        },
    ],
    skills: ['JavaScript', 'TypeScript', 'React', 'Node.js', 'Python'],
};

// Generate preview HTML
const previewHtml = computed(() => {
    const styles = form.value.styles;
    
    let html = `
        <div style="
            font-family: ${styles.fontFamily};
            font-size: ${styles.fontSize};
            line-height: ${styles.lineHeight};
            color: ${styles.textColor};
            background-color: ${styles.backgroundColor};
            padding: 32px;
            min-height: 100%;
        ">
    `;

    const enabledSections = form.value.sections
        .filter(s => s.enabled)
        .sort((a, b) => a.order - b.order);

    for (const section of enabledSections) {
        html += generateSectionHtml(section.id, styles);
    }

    html += '</div>';
    return html;
});

const generateSectionHtml = (sectionId: string, styles: Styles): string => {
    const sectionStyle = `
        margin-bottom: 24px;
    `;
    const titleStyle = `
        color: ${styles.primaryColor};
        font-family: ${styles.headingFont};
        border-bottom: 2px solid ${styles.primaryColor};
        padding-bottom: 8px;
        margin-bottom: 16px;
        font-size: 1.1em;
        font-weight: 600;
    `;

    switch (sectionId) {
        case 'header':
            return `
                <div style="${sectionStyle} text-align: ${form.value.layout.headerStyle === 'centered' ? 'center' : 'left'};">
                    <h1 style="color: ${styles.headingColor}; margin: 0 0 8px 0; font-size: 1.8em;">${sampleData.personal_info.full_name}</h1>
                    <p style="color: ${styles.secondaryColor}; margin: 0;">
                        ${sampleData.personal_info.email} | ${sampleData.personal_info.phone} | ${sampleData.personal_info.location}
                    </p>
                </div>
            `;
        case 'summary':
            return `
                <div style="${sectionStyle}">
                    <h2 style="${titleStyle}">Professional Summary</h2>
                    <p style="margin: 0;">${sampleData.summary}</p>
                </div>
            `;
        case 'experience':
            return `
                <div style="${sectionStyle}">
                    <h2 style="${titleStyle}">Work Experience</h2>
                    ${sampleData.experience.map(exp => `
                        <div style="margin-bottom: 12px;">
                            <h4 style="margin: 0; color: ${styles.headingColor};">${exp.title}</h4>
                            <p style="margin: 4px 0; color: ${styles.secondaryColor};">${exp.company} | ${exp.location}</p>
                            <p style="margin: 4px 0; font-size: 0.9em; color: ${styles.secondaryColor};">${exp.start_date} - ${exp.end_date}</p>
                            <p style="margin: 4px 0;">${exp.description}</p>
                        </div>
                    `).join('')}
                </div>
            `;
        case 'education':
            return `
                <div style="${sectionStyle}">
                    <h2 style="${titleStyle}">Education</h2>
                    ${sampleData.education.map(edu => `
                        <div style="margin-bottom: 12px;">
                            <h4 style="margin: 0; color: ${styles.headingColor};">${edu.degree}</h4>
                            <p style="margin: 4px 0; color: ${styles.secondaryColor};">${edu.institution}</p>
                            <p style="margin: 4px 0; font-size: 0.9em; color: ${styles.secondaryColor};">${edu.graduation_date}</p>
                        </div>
                    `).join('')}
                </div>
            `;
        case 'skills':
            return `
                <div style="${sectionStyle}">
                    <h2 style="${titleStyle}">Skills</h2>
                    <div style="display: flex; flex-wrap: wrap; gap: 8px;">
                        ${sampleData.skills.map(skill => `
                            <span style="
                                background: ${styles.accentColor}20;
                                color: ${styles.accentColor};
                                padding: 4px 12px;
                                border-radius: ${styles.borderRadius};
                                font-size: 0.9em;
                            ">${skill}</span>
                        `).join('')}
                    </div>
                </div>
            `;
        default:
            return `<div style="${sectionStyle}"><h2 style="${titleStyle}">${sectionId}</h2><p>Section content...</p></div>`;
    }
};

const save = async () => {
    saving.value = true;
    
    try {
        const url = isEditing.value 
            ? templateUpdate.url(props.template?.id as number)
            : templateStore.url();
        
        // Use FormData for file uploads
        const formData = new FormData();
        
        // Append all form fields
        formData.append('name', form.value.name);
        formData.append('description', form.value.description || '');
        formData.append('category', form.value.category);
        formData.append('is_premium', form.value.is_premium ? '1' : '0');
        formData.append('price', form.value.price?.toString() || '');
        formData.append('currency', form.value.currency);
        formData.append('is_active', form.value.is_active ? '1' : '0');
        formData.append('layout', JSON.stringify(form.value.layout));
        formData.append('styles', JSON.stringify(form.value.styles));
        formData.append('sections', JSON.stringify(form.value.sections));
        formData.append('html_content', form.value.html_content || '');
        formData.append('css_content', form.value.css_content || '');
        
        // Append image if selected
        if (imageFile.value) {
            formData.append('image', imageFile.value);
        }
        
        // For PUT requests with FormData, we need to use POST with _method override
        if (isEditing.value) {
            formData.append('_method', 'PUT');
        }
        
        const response = await fetch(url, {
            method: 'POST', // Always POST for FormData (use _method for PUT)
            headers: {
                'Accept': 'application/json',
                'X-XSRF-TOKEN': decodeURIComponent(document.cookie.split('; ').find(row => row.startsWith('XSRF-TOKEN='))?.split('=')[1] || ''),
            },
            body: formData,
        });

        const data = await response.json();

        if (data.success) {
            addToast({ 
                type: 'success', 
                title: 'Success', 
                message: isEditing.value ? 'Template updated!' : 'Template created!' 
            });
            
            // Update the image preview if a new image was uploaded
            if (data.template?.image) {
                imagePreview.value = `/storage/${data.template.image}`;
                imageFile.value = null;
            }
            
            if (!isEditing.value) {
                router.visit(templateEdit.url(data.template.id));
            }
        } else {
            addToast({ type: 'error', title: 'Error', message: data.message || 'Failed to save template.' });
        }
    } catch (e) {
        addToast({ type: 'error', title: 'Error', message: 'Failed to save template.' });
    } finally {
        saving.value = false;
    }
};

const breadcrumbs = computed(() => [
    { title: 'Admin', href: adminDashboard.url() },
    { title: 'Templates', href: adminTemplates.url() },
    { title: isEditing.value ? 'Edit Template' : 'New Template' },
]);
</script>

<template>
    <Head :title="isEditing ? `Edit: ${form.name}` : 'New Template'" />
    
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex flex-col h-full">
            <!-- Header -->
            <div class="border-b bg-background/95 backdrop-blur supports-[backdrop-filter]:bg-background/60 sticky top-0 z-10">
                <div class="flex items-center justify-between p-4">
                    <div class="flex items-center gap-4">
                        <Button variant="ghost" size="icon" as-child>
                            <Link :href="adminTemplates.url()">
                                <ChevronLeft class="h-5 w-5" />
                            </Link>
                        </Button>
                        <div>
                            <h1 class="text-lg font-semibold">
                                {{ isEditing ? 'Edit Template' : 'Create Template' }}
                            </h1>
                            <p class="text-sm text-muted-foreground">
                                Design your CV template with drag & drop
                            </p>
                        </div>
                    </div>
                    <div class="flex items-center gap-2">
                        <Button variant="outline" @click="previewMode = !previewMode">
                            <Eye class="h-4 w-4 mr-2" />
                            {{ previewMode ? 'Edit' : 'Preview' }}
                        </Button>
                        <Button :disabled="saving || !form.name" @click="save">
                            <Save class="h-4 w-4 mr-2" />
                            {{ saving ? 'Saving...' : 'Save Template' }}
                        </Button>
                    </div>
                </div>
            </div>

            <!-- Main Content -->
            <div class="flex-1 flex overflow-hidden">
                <!-- Sidebar -->
                <div class="w-80 border-r bg-muted/20 overflow-y-auto p-4 space-y-4">
                    <Tabs v-model="activeTab" class="w-full">
                        <TabsList class="grid w-full grid-cols-3">
                            <TabsTrigger value="design">
                                <Layout class="h-4 w-4" />
                            </TabsTrigger>
                            <TabsTrigger value="style">
                                <Palette class="h-4 w-4" />
                            </TabsTrigger>
                            <TabsTrigger value="settings">
                                <Settings class="h-4 w-4" />
                            </TabsTrigger>
                        </TabsList>

                        <!-- Design Tab -->
                        <TabsContent value="design" class="space-y-4 mt-4">
                            <div>
                                <Label class="text-xs font-medium uppercase tracking-wider text-muted-foreground">
                                    Sections
                                </Label>
                                <p class="text-xs text-muted-foreground mb-2">
                                    Drag to reorder, toggle to enable/disable
                                </p>
                            </div>

                            <div class="space-y-2">
                                <div
                                    v-for="(section, index) in form.sections"
                                    :key="section.id"
                                    :draggable="true"
                                    @dragstart="handleDragStart(index)"
                                    @dragover="handleDragOver"
                                    @drop="handleDrop(index)"
                                    class="flex items-center gap-2 p-2 rounded-lg border bg-background cursor-move transition-all"
                                    :class="{ 'opacity-50': !section.enabled }"
                                >
                                    <GripVertical class="h-4 w-4 text-muted-foreground shrink-0" />
                                    <span class="flex-1 text-sm">{{ section.name }}</span>
                                    <Switch 
                                        :checked="section.enabled" 
                                        @update:checked="toggleSection(index)"
                                        class="scale-75"
                                    />
                                </div>
                            </div>

                            <div class="pt-4 space-y-3">
                                <Label>Header Style</Label>
                                <Select v-model="form.layout.headerStyle">
                                    <SelectTrigger>
                                        <SelectValue />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem 
                                            v-for="style in headerStyles" 
                                            :key="style.value" 
                                            :value="style.value"
                                        >
                                            {{ style.label }}
                                        </SelectItem>
                                    </SelectContent>
                                </Select>
                            </div>

                            <div class="space-y-3">
                                <Label>Section Style</Label>
                                <Select v-model="form.layout.sectionStyle">
                                    <SelectTrigger>
                                        <SelectValue />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem 
                                            v-for="style in sectionStyles" 
                                            :key="style.value" 
                                            :value="style.value"
                                        >
                                            {{ style.label }}
                                        </SelectItem>
                                    </SelectContent>
                                </Select>
                            </div>
                        </TabsContent>

                        <!-- Style Tab -->
                        <TabsContent value="style" class="space-y-4 mt-4">
                            <div class="space-y-3">
                                <Label>Primary Color</Label>
                                <div class="flex gap-2">
                                    <input 
                                        type="color" 
                                        v-model="form.styles.primaryColor"
                                        class="w-10 h-10 rounded cursor-pointer border-0"
                                    />
                                    <Input v-model="form.styles.primaryColor" class="flex-1" />
                                </div>
                            </div>

                            <div class="space-y-3">
                                <Label>Secondary Color</Label>
                                <div class="flex gap-2">
                                    <input 
                                        type="color" 
                                        v-model="form.styles.secondaryColor"
                                        class="w-10 h-10 rounded cursor-pointer border-0"
                                    />
                                    <Input v-model="form.styles.secondaryColor" class="flex-1" />
                                </div>
                            </div>

                            <div class="space-y-3">
                                <Label>Accent Color</Label>
                                <div class="flex gap-2">
                                    <input 
                                        type="color" 
                                        v-model="form.styles.accentColor"
                                        class="w-10 h-10 rounded cursor-pointer border-0"
                                    />
                                    <Input v-model="form.styles.accentColor" class="flex-1" />
                                </div>
                            </div>

                            <div class="space-y-3">
                                <Label>Background Color</Label>
                                <div class="flex gap-2">
                                    <input 
                                        type="color" 
                                        v-model="form.styles.backgroundColor"
                                        class="w-10 h-10 rounded cursor-pointer border-0"
                                    />
                                    <Input v-model="form.styles.backgroundColor" class="flex-1" />
                                </div>
                            </div>

                            <div class="space-y-3">
                                <Label>Text Color</Label>
                                <div class="flex gap-2">
                                    <input 
                                        type="color" 
                                        v-model="form.styles.textColor"
                                        class="w-10 h-10 rounded cursor-pointer border-0"
                                    />
                                    <Input v-model="form.styles.textColor" class="flex-1" />
                                </div>
                            </div>

                            <div class="space-y-3">
                                <Label>Body Font</Label>
                                <Select v-model="form.styles.fontFamily">
                                    <SelectTrigger>
                                        <SelectValue />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem 
                                            v-for="font in fonts" 
                                            :key="font.value" 
                                            :value="font.value"
                                        >
                                            {{ font.label }}
                                        </SelectItem>
                                    </SelectContent>
                                </Select>
                            </div>

                            <div class="space-y-3">
                                <Label>Heading Font</Label>
                                <Select v-model="form.styles.headingFont">
                                    <SelectTrigger>
                                        <SelectValue />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem 
                                            v-for="font in fonts" 
                                            :key="font.value" 
                                            :value="font.value"
                                        >
                                            {{ font.label }}
                                        </SelectItem>
                                    </SelectContent>
                                </Select>
                            </div>

                            <div class="space-y-3">
                                <Label>Font Size</Label>
                                <Select v-model="form.styles.fontSize">
                                    <SelectTrigger>
                                        <SelectValue />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="12px">Small (12px)</SelectItem>
                                        <SelectItem value="14px">Medium (14px)</SelectItem>
                                        <SelectItem value="16px">Large (16px)</SelectItem>
                                    </SelectContent>
                                </Select>
                            </div>
                        </TabsContent>

                        <!-- Settings Tab -->
                        <TabsContent value="settings" class="space-y-4 mt-4">
                            <div class="space-y-2">
                                <Label>Template Name *</Label>
                                <Input v-model="form.name" placeholder="e.g., Modern Professional" />
                            </div>

                            <div class="space-y-2">
                                <Label>Description</Label>
                                <Textarea 
                                    v-model="form.description" 
                                    placeholder="Describe this template..."
                                    rows="3"
                                />
                            </div>

                            <div class="space-y-2">
                                <Label>Category</Label>
                                <Select v-model="form.category">
                                    <SelectTrigger>
                                        <SelectValue />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem 
                                            v-for="(label, value) in categories" 
                                            :key="value" 
                                            :value="value"
                                        >
                                            {{ label }}
                                        </SelectItem>
                                    </SelectContent>
                                </Select>
                            </div>

                            <div class="flex items-center justify-between py-2">
                                <div>
                                    <Label>Premium Template</Label>
                                    <p class="text-xs text-muted-foreground">Requires paid plan</p>
                                </div>
                                <Switch v-model:checked="form.is_premium" />
                            </div>

                            <div v-if="form.is_premium" class="space-y-2">
                                <Label>Price</Label>
                                <div class="flex gap-2">
                                    <Input 
                                        v-model="form.price" 
                                        type="number" 
                                        placeholder="0.00"
                                        class="flex-1"
                                    />
                                    <Select v-model="form.currency" class="w-24">
                                        <SelectTrigger>
                                            <SelectValue />
                                        </SelectTrigger>
                                        <SelectContent>
                                            <SelectItem value="USD">USD</SelectItem>
                                            <SelectItem value="EUR">EUR</SelectItem>
                                            <SelectItem value="XAF">XAF</SelectItem>
                                        </SelectContent>
                                    </Select>
                                </div>
                            </div>

                            <div class="flex items-center justify-between py-2">
                                <div>
                                    <Label>Active</Label>
                                    <p class="text-xs text-muted-foreground">Visible to users</p>
                                </div>
                                <Switch v-model:checked="form.is_active" />
                            </div>

                            <!-- Template Image Upload -->
                            <div class="space-y-3 pt-4 border-t">
                                <Label>Template Preview Image</Label>
                                <p class="text-xs text-muted-foreground">
                                    Upload an image to show in the template gallery. Recommended: 600x800px
                                </p>
                                
                                <div v-if="imagePreview" class="relative group">
                                    <img 
                                        :src="imagePreview" 
                                        alt="Template preview" 
                                        class="w-full aspect-[3/4] object-cover rounded-lg border"
                                    />
                                    <div class="absolute inset-0 bg-black/50 opacity-0 group-hover:opacity-100 transition-opacity rounded-lg flex items-center justify-center gap-2">
                                        <Button 
                                            size="sm" 
                                            variant="secondary"
                                            @click="imageInput?.click()"
                                        >
                                            <ImageIcon class="h-4 w-4 mr-1" />
                                            Change
                                        </Button>
                                        <Button 
                                            size="sm" 
                                            variant="destructive"
                                            @click="removeImage"
                                        >
                                            <Trash2 class="h-4 w-4 mr-1" />
                                            Remove
                                        </Button>
                                    </div>
                                </div>
                                
                                <div 
                                    v-else
                                    class="border-2 border-dashed rounded-lg p-6 text-center cursor-pointer hover:border-primary/50 transition-colors"
                                    @click="imageInput?.click()"
                                >
                                    <ImageIcon class="h-10 w-10 mx-auto text-muted-foreground mb-2" />
                                    <p class="text-sm font-medium">Click to upload image</p>
                                    <p class="text-xs text-muted-foreground mt-1">PNG, JPG, WebP up to 2MB</p>
                                </div>
                                
                                <input 
                                    ref="imageInput"
                                    type="file" 
                                    accept="image/jpeg,image/png,image/jpg,image/gif,image/webp"
                                    class="hidden"
                                    @change="handleImageSelect"
                                />
                            </div>
                        </TabsContent>
                    </Tabs>
                </div>

                <!-- Preview Area -->
                <div class="flex-1 overflow-auto bg-muted/30 p-8">
                    <div class="max-w-[800px] mx-auto">
                        <Card class="shadow-xl">
                            <CardContent class="p-0">
                                <div 
                                    class="aspect-[8.5/11] overflow-hidden"
                                    v-html="previewHtml"
                                ></div>
                            </CardContent>
                        </Card>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<style scoped>
/* Drag and drop visual feedback */
[draggable="true"]:active {
    opacity: 0.7;
}
</style>
