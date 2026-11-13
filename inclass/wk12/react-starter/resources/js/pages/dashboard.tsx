import { Head, Link, usePage } from '@inertiajs/react';
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardDescription,
    CardFooter,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { dashboard } from '@/routes';
import { edit as editProfile } from '@/routes/profile';
import { index as students } from '@/routes/students';

export default function Dashboard() {
    const { auth } = usePage().props;

    return (
        <>
            <Head title="Dashboard" />
            <div className="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">
                <div className="grid auto-rows-min gap-4 md:grid-cols-3">
                    <Card>
                        <CardHeader>
                            <CardTitle>Students</CardTitle>
                            <CardDescription>
                                The class list, sent by a Laravel controller
                                as props.
                            </CardDescription>
                        </CardHeader>
                        <CardFooter>
                            <Button asChild>
                                <Link href={students()}>View students</Link>
                            </Button>
                        </CardFooter>
                    </Card>

                    <Card>
                        <CardHeader>
                            <CardTitle>Signed in</CardTitle>
                            <CardDescription>
                                Laravel shares the user with every page as{' '}
                                <code>auth.user</code>.
                            </CardDescription>
                        </CardHeader>
                        <CardContent>
                            <p className="font-medium">{auth.user.name}</p>
                            <p className="text-sm text-muted-foreground">
                                {auth.user.email}
                            </p>
                        </CardContent>
                        <CardFooter>
                            <Button asChild variant="outline">
                                <Link href={editProfile()}>Edit profile</Link>
                            </Button>
                        </CardFooter>
                    </Card>

                    <Card>
                        <CardHeader>
                            <CardTitle>How this page got here</CardTitle>
                            <CardDescription>
                                The Inertia round trip, in three steps.
                            </CardDescription>
                        </CardHeader>
                        <CardContent>
                            <ol className="list-decimal space-y-1 pl-5 text-sm">
                                <li>
                                    <code>routes/web.php</code> matched{' '}
                                    <code>/dashboard</code>.
                                </li>
                                <li>
                                    Laravel answered with a page name and its
                                    props.
                                </li>
                                <li>
                                    React rendered{' '}
                                    <code>pages/dashboard.tsx</code>.
                                </li>
                            </ol>
                        </CardContent>
                    </Card>
                </div>
            </div>
        </>
    );
}

Dashboard.layout = {
    breadcrumbs: [
        {
            title: 'Dashboard',
            href: dashboard(),
        },
    ],
};
