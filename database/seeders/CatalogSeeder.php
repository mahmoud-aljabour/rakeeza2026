<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Project;
use App\Models\Service;
use App\Scopes\ActiveScope;
use App\Services\SettingService;
use Illuminate\Database\Seeder;

final class CatalogSeeder extends Seeder
{
    public function run(): void
    {
        Service::query()
            ->withoutGlobalScope(ActiveScope::class)
            ->whereIn('title', ['Plumbing', 'Hidden'])
            ->delete();

        $services = [
            [
                'title' => 'خدمات النظافة',
                'description' => 'نقدم خدمات تنظيف شاملة للمنازل، المنشآت، والمشاريع، من خلال فريق مدرب، باستخدام معدات احترافية ومواد عالية الجودة لضمان بيئة صحية وآمنة.',
                'image_path' => 'images/services/cleaning.webp',
            ],
            [
                'title' => 'خدمات إزالة الركام',
                'description' => 'نقوم بإزالة مخلفات البناء والركام من مواقع الترميم والإعمار، مع توفير نقل آمن ومنظم والتخلص من المخلفات بطريقة سليمة وصديقة للبيئة.',
                'image_path' => 'images/services/debris.webp',
            ],
            [
                'title' => 'خدمات الصحة والسلامة',
                'description' => 'حلول متكاملة لضمان بيئة عمل وسكن آمنة، تشمل: تعقيم وتطهير، مكافحة الحشرات، وتوفير كافة تجهيزات السلامة المهنية المعتمدة.',
                'image_path' => 'images/services/safety.webp',
            ],
            [
                'title' => 'خدمات الصيانة العامة',
                'description' => 'نقدم خدمات الصيانة الشاملة (كهرباء، سباكة، وغيرها) إلى جانب الإصلاحات العامة للمنازل والمنشآت، بطريقة منظمة تضمن جودة التنفيذ وسرعة الخدمة.',
                'image_path' => 'images/services/maintenance.webp',
            ],
            [
                'title' => 'خدمات للمؤسسات والمنظمات',
                'description' => 'تشغيل ميداني يشمل إدارة الفرق الفنية، توريد العمالة، إنشاء وتجهيز المساحات، وتنفيذ أعمال التشطيب والصيانة بإشراف مباشر ونقطة اتصال واحدة.',
                'image_path' => 'images/services/organizations.webp',
            ],
            [
                'title' => 'التصميم الداخلي والديكور',
                'description' => 'هل تجد صعوبة في إنشاء الحل الداخلي الذي يدور في رأسك؟ يساعدك خبراء التصميم الداخلي لدينا في تجسيد أفكارك وتحويلها إلى واقع ملموس وأنيق.',
                'image_path' => 'images/services/interior.jpg',
            ],
        ];

        foreach ($services as $service) {
            Service::query()->updateOrCreate(
                ['title' => $service['title']],
                [
                    'description' => $service['description'],
                    'image_path' => $service['image_path'],
                    'is_active' => true,
                ],
            );
        }

        $serviceIds = Service::query()
            ->whereIn('title', [
                'خدمات النظافة',
                'خدمات الصيانة العامة',
                'خدمات للمؤسسات والمنظمات',
            ])
            ->pluck('id', 'title');

        $projects = [
            [
                'title' => 'أعمال السباكة والصيانة الداخلية',
                'details' => 'تنفيذ أعمال السباكة والصيانة الداخلية بجودة عالية وإشراف مباشر يضمن سرعة الإنجاز ودقة التنفيذ.',
                'image_path' => 'images/projects/project-1.jpg',
                'order_column' => 1,
                'service_id' => $serviceIds['خدمات الصيانة العامة'] ?? null,
            ],
            [
                'title' => 'تجهيز المواقع والمساحات الميدانية',
                'details' => 'تجهيز المواقع والمساحات الميدانية وفق متطلبات التشغيل، مع تنظيم الفرق وتسليم جاهز للاستخدام.',
                'image_path' => 'images/projects/project-2.jpg',
                'order_column' => 2,
                'service_id' => $serviceIds['خدمات للمؤسسات والمنظمات'] ?? null,
            ],
            [
                'title' => 'تنظيف وتأهيل المنشآت',
                'details' => 'تنفيذ أعمال النظافة الشاملة للمنشآت والمواقع، مع فريق مدرب ومعدات احترافية لضمان بيئة صحية وآمنة.',
                'image_path' => 'images/services/cleaning.webp',
                'order_column' => 3,
                'service_id' => $serviceIds['خدمات النظافة'] ?? null,
            ],
        ];

        foreach ($projects as $project) {
            Project::query()->updateOrCreate(
                ['title' => $project['title']],
                [
                    'details' => $project['details'],
                    'image_path' => $project['image_path'],
                    'image_paths' => [$project['image_path']],
                    'order_column' => $project['order_column'],
                    'service_id' => $project['service_id'],
                ],
            );
        }

        app(SettingService::class)->setMany(config('rakeeza.defaults'));
    }
}
